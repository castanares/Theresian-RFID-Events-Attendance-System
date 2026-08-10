<?php
require_once __DIR__ . '/dbcon.php';
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) session_start();

$out = ['logged' => false];
// include simple session info
if (!empty($_SESSION['account_type']) || !empty($_SESSION['user_id'])) {
    $out['logged'] = !empty($_SESSION['account_type']);
    if (!empty($_SESSION['account_type'])) $out['account_type'] = $_SESSION['account_type'];
    $out['user_id'] = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // attempt to read user's department from the user table when DB available
    if (isset($conn) && ($conn instanceof mysqli) && !empty($out['user_id'])) {
        // helper to find a column by candidates (exact or fuzzy)
        $findCol = function($table, $cands) use ($conn) {
            $res = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($table) . "`");
            if (!$res) return null;
            $cols = [];
            while ($r = $res->fetch_assoc()) $cols[] = $r['Field'];
            foreach ($cands as $c) if (in_array($c, $cols, true)) return $c;
            foreach ($cols as $col) {
                foreach ($cands as $c) if (stripos($col, $c) !== false) return $col;
            }
            return null;
        };

        // find a plausible user table
        $userTable = null;
        $resT = $conn->query("SHOW TABLES");
        if ($resT) while ($r = $resT->fetch_row()) { if (stripos($r[0],'user') !== false) { $userTable = $r[0]; break; } }
        if ($userTable) {
            // candidate columns to use for identifying the user row
            $idCandidates = ['id','user_id','uid','ID'];
            $loginCandidates = ['username','user_name','user','email','account_name','account','name'];
            $userDeptCol = $findCol($userTable, ['departments_id','department_id','dept_id','department','dept']);

            // We'll attempt to find the user row by several methods:
            // 1. If session user id is numeric, try numeric id columns first
            // 2. Try username/email columns
            // 3. Fallback: try any candidate identifier column
            $sessId = $out['user_id'];
            $foundRow = null;
            $usedIdCol = null;

            // Helper to try fetch by column
            $tryFetch = function($col, $val, $isInt=false) use ($conn, $userTable, &$foundRow, &$usedIdCol) {
                $usedIdCol = $col;
                $sql = "SELECT * FROM `" . $conn->real_escape_string($userTable) . "` WHERE `" . $conn->real_escape_string($col) . "` = ? LIMIT 1";
                $stmt = $conn->prepare($sql);
                if (!$stmt) return false;
                if ($isInt) $stmt->bind_param('i', $val); else $stmt->bind_param('s', $val);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $foundRow = $row;
                }
                $stmt->close();
                return $foundRow !== null;
            };

            // 1) numeric session id
            if ($sessId !== null && ctype_digit((string)$sessId)) {
                foreach ($idCandidates as $c) {
                    $col = $findCol($userTable, [$c]);
                    if ($col && $tryFetch($col, (int)$sessId, true)) break;
                }
            }

            // 2) try login/username/email columns
            if ($foundRow === null && $sessId !== null) {
                foreach ($loginCandidates as $c) {
                    $col = $findCol($userTable, [$c]);
                    if ($col && $tryFetch($col, $sessId, false)) break;
                }
            }

            // 3) fallback: try any id candidate as string match
            if ($foundRow === null && $sessId !== null) {
                foreach ($idCandidates as $c) {
                    $col = $findCol($userTable, [$c]);
                    if ($col && $tryFetch($col, $sessId, false)) break;
                }
            }

            // 4) last resort: try match against any column that contains the session string
            if ($foundRow === null && $sessId !== null) {
                $resCols = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($userTable) . "`");
                if ($resCols) while ($rc = $resCols->fetch_assoc()) {
                    $colName = $rc['Field'];
                    // skip binary/blob columns
                    if (stripos($rc['Type'],'blob') !== false) continue;
                    if ($tryFetch($colName, $sessId, false)) break;
                }
            }

            if ($foundRow !== null) {
                // extract department value from user row
                $deptVal = null;
                if ($userDeptCol && array_key_exists($userDeptCol, $foundRow)) $deptVal = $foundRow[$userDeptCol];
                else {
                    // try find any column that looks like department
                    $maybeDept = $findCol($userTable, ['depart','dept','department','departments']);
                    if ($maybeDept && array_key_exists($maybeDept, $foundRow)) $deptVal = $foundRow[$maybeDept];
                }
                $out['department'] = $deptVal;

                // Normalize tokens and resolve to numeric ids similar to before
                $tokens = [];
                if (is_array($deptVal)) $tokens = array_map('strval', $deptVal);
                else $tokens = array_filter(array_map('trim', explode(',', strval($deptVal))), 'strlen');

                $numeric = [];
                $names = [];
                foreach ($tokens as $t) {
                    if ($t === '') continue;
                    if (ctype_digit($t)) $numeric[] = (int)$t;
                    else $names[] = $t;
                }

                $resolved = $numeric;

                // find departments table to resolve names/acronyms
                $deptTable = null;
                $resT2 = $conn->query("SHOW TABLES");
                if ($resT2) while ($r2 = $resT2->fetch_row()) if (stripos($r2[0],'depart') !== false) { $deptTable = $r2[0]; break; }
                if ($deptTable && !empty($names)) {
                    $deptIdCol = $findCol($deptTable, ['id','dept_id','Dept_ID','departments_id']);
                    $deptNameCol = $findCol($deptTable, ['dept_name','Dept_name','name']);
                    $deptAcr = $findCol($deptTable, ['dept_Acronym','dept_acronym','acronym']);
                    foreach ($names as $nm) {
                        $nmLow = mb_strtolower($nm);
                        if ($deptNameCol) {
                            $q = $conn->prepare("SELECT `$deptIdCol` FROM `" . $conn->real_escape_string($deptTable) . "` WHERE LOWER(`" . $conn->real_escape_string($deptNameCol) . "`) = ? LIMIT 1");
                            if ($q) { $q->bind_param('s', $nmLow); $q->execute(); $rN = $q->get_result()->fetch_row(); if ($rN) $resolved[] = (int)$rN[0]; $q->close(); }
                        }
                        if ($deptAcr) {
                            $q2 = $conn->prepare("SELECT `$deptIdCol` FROM `" . $conn->real_escape_string($deptTable) . "` WHERE LOWER(`" . $conn->real_escape_string($deptAcr) . "`) = ? LIMIT 1");
                            if ($q2) { $q2->bind_param('s', $nmLow); $q2->execute(); $rA = $q2->get_result()->fetch_row(); if ($rA) $resolved[] = (int)$rA[0]; $q2->close(); }
                        }
                    }
                }

                $resolved = array_values(array_unique($resolved));
                // attach department ids and CSV
                if (count($resolved) === 1) {
                    $out['department_id'] = (int)$resolved[0];
                    $out['departments_id'] = (string)$resolved[0];
                } elseif (count($resolved) > 1) {
                    $out['department_id'] = $resolved;
                    $out['departments_id'] = implode(',', array_map('strval', $resolved));
                } else {
                    if (count($tokens) === 1 && ctype_digit($tokens[0])) {
                        $out['department_id'] = (int)$tokens[0];
                        $out['departments_id'] = (string)$tokens[0];
                    } else {
                        $out['department_id'] = $deptVal;
                        $out['departments_id'] = is_string($deptVal) ? $deptVal : json_encode($deptVal);
                    }
                }

                // fetch department_info rows when we have numeric ids
                $out['department_info'] = null;
                if (!empty($resolved) && $deptTable) {
                    $deptIdCol = $deptIdCol ?? $findCol($deptTable, ['id','dept_id','Dept_ID','departments_id']);
                    $deptNameCol = $deptNameCol ?? $findCol($deptTable, ['dept_name','Dept_name','name']);
                    $deptAcr = $deptAcr ?? $findCol($deptTable, ['dept_Acronym','dept_acronym','acronym']);
                    $infos = [];
                    foreach ($resolved as $rid) {
                        $q = $conn->prepare("SELECT * FROM `" . $conn->real_escape_string($deptTable) . "` WHERE `" . $conn->real_escape_string($deptIdCol) . "` = ? LIMIT 1");
                        if ($q) { $q->bind_param('i', $rid); $q->execute(); $rD = $q->get_result()->fetch_assoc(); if ($rD) {
                            $infos[] = [
                                'id' => isset($rD[$deptIdCol]) ? (int)$rD[$deptIdCol] : null,
                                'name' => ($deptNameCol && isset($rD[$deptNameCol])) ? $rD[$deptNameCol] : ($rD['name'] ?? null),
                                'acronym' => ($deptAcr && isset($rD[$deptAcr])) ? $rD[$deptAcr] : null,
                                'raw' => $rD
                            ];
                        } $q->close(); }
                    }
                    if (!empty($infos)) $out['department_info'] = (count($infos)===1? $infos[0] : $infos);
                }
                // expose sanitized user row as `data` for API consumers (strip common sensitive fields)
                $sanitized = $foundRow;
                $sensitive = ['password','pass','pwd','pw','secret','token','salt'];
                foreach ($sanitized as $col => $val) {
                    foreach ($sensitive as $bad) {
                        if (stripos($col, $bad) !== false) { unset($sanitized[$col]); break; }
                    }
                }
                $out['data'] = $sanitized;

                // detect any picture/avatar-like column and expose as profile_pic (top-level and inside data)
                $picCandidates = ['profile_pic','profile_picture','avatar','picture','photo','image','img','picture_url','avatar_url'];
                foreach ($picCandidates as $pc) {
                    if (array_key_exists($pc, $sanitized) && strlen(trim((string)$sanitized[$pc])) > 0) {
                        $out['profile_pic'] = $sanitized[$pc];
                        break;
                    }
                }
                if (empty($out['profile_pic'])) {
                    foreach ($sanitized as $col => $val) {
                        if (stripos($col,'photo') !== false || stripos($col,'avatar') !== false || stripos($col,'image') !== false) {
                            if (strlen(trim((string)$val)) > 0) { $out['profile_pic'] = $val; break; }
                        }
                    }
                }
            }
        }
    }
}

// debug info
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    if (!isset($out['_debug'])) $out['_debug'] = [];
    $out['_debug']['session_user_id'] = $_SESSION['user_id'] ?? null;
}

echo json_encode($out);
exit;

?>
