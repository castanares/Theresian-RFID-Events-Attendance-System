<?php
use App\Config\Config;
use App\Core\Csrf;
?>
<div class="w-full max-w-4xl bg-white rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden grid grid-cols-1 md:grid-cols-2"
     x-data="loginHandler()">
  
  <!-- Left Institutional Brand Panel -->
  <div class="bg-gradient-to-br from-maroon-900 via-maroon-950 to-maroon-900 p-8 sm:p-12 text-white flex flex-col justify-between relative overflow-hidden">
    <!-- Subtle Background Accent -->
    <div class="absolute -right-16 -top-16 w-64 h-64 bg-maroon-700/20 rounded-full blur-3xl pointer-events-none"></div>

    <div>
      <div class="flex items-center gap-3">
        <img src="<?= Config::BASE_URL ?>/images/stctandag-logo.png" alt="STC Logo" class="w-14 h-14 object-contain drop-shadow-md">
        <div>
          <h2 class="font-bold text-lg leading-tight tracking-wide">Saint Theresa College</h2>
          <p class="text-xs text-maroon-200 font-medium">Tandag City, Surigao del Sur</p>
        </div>
      </div>

      <div class="mt-12 space-y-3">
        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-maroon-200 border border-white/10">
          OSAS Attendance Portal
        </span>
        <h3 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
          Smart RFID Event Management
        </h3>
        <p class="text-sm text-maroon-200/90 leading-relaxed">
          Fast, automated, and accurate event attendance logging for students and faculty.
        </p>
      </div>
    </div>

    <div class="pt-8 border-t border-maroon-800/80 text-xs text-maroon-300 flex items-center justify-between">
      <span>Version <?= Config::APP_VERSION ?></span>
      <span>&copy; <?= date('Y') ?> STC Tandag</span>
    </div>
  </div>

  <!-- Right Form Panel -->
  <div class="p-8 sm:p-12 flex flex-col justify-center">
    
    <div class="mb-8">
      <h3 class="text-2xl font-bold text-slate-900 tracking-tight">Welcome Back</h3>
      <p class="text-sm text-slate-500 mt-1">Please enter your assigned credentials to sign in.</p>
    </div>

    <!-- Error Alert Box -->
    <div x-show="errorMessage" x-cloak 
         class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-start gap-3">
      <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5"></i>
      <span x-text="errorMessage"></span>
    </div>

    <!-- Sign In Form -->
    <form @submit.prevent="submitLogin" class="space-y-5">
      <?= Csrf::field() ?>

      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Username</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <i data-lucide="user" class="w-5 h-5"></i>
          </div>
          <input type="text" 
                 x-model="form.username" 
                 required 
                 placeholder="e.g. OSAS-Admin" 
                 class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-maroon-700/30 focus:border-maroon-700 transition">
        </div>
      </div>

      <div>
        <div class="flex items-center justify-between mb-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Password</label>
          <button type="button" @click="otpModal = true" class="text-xs font-semibold text-maroon-700 hover:text-maroon-900 transition">
            Forgot password?
          </button>
        </div>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <i data-lucide="lock" class="w-5 h-5"></i>
          </div>
          <input :type="showPass ? 'text' : 'password'" 
                 x-model="form.password" 
                 required 
                 placeholder="••••••••" 
                 class="w-full pl-11 pr-11 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-maroon-700/30 focus:border-maroon-700 transition">
          <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
            <i :data-lucide="showPass ? 'eye-off' : 'eye'" class="w-5 h-5"></i>
          </button>
        </div>
      </div>

      <button type="submit" 
              :disabled="loading" 
              class="w-full py-3.5 px-4 bg-maroon-900 hover:bg-maroon-800 text-white font-semibold rounded-xl text-sm shadow-md hover:shadow-lg transition active:scale-[0.99] disabled:opacity-70 flex items-center justify-center gap-2">
        <span x-show="!loading">Sign In to Dashboard</span>
        <span x-show="loading" x-cloak class="flex items-center gap-2">
          <svg class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
          </svg>
          Authenticating...
        </span>
      </button>

      <p class="text-center text-xs text-slate-400 mt-4">
        Need assistance? Contact the OSAS Administrator.
      </p>
    </form>

  </div>

  <!-- Forgot Password / OTP Modal -->
  <div x-show="otpModal" x-cloak 
       class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
       x-transition>
    <div @click.away="otpModal = false" class="w-full max-w-md bg-white rounded-2xl p-6 sm:p-8 shadow-2xl border border-slate-100">
      
      <!-- Step 1: Request OTP -->
      <div x-show="otpStep === 1">
        <div class="flex items-center justify-between mb-4">
          <h4 class="text-lg font-bold text-slate-900">Reset Password</h4>
          <button @click="otpModal = false" class="text-slate-400 hover:text-slate-600">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>
        <p class="text-xs text-slate-500 mb-5">Enter your username and email address to receive a secure 6-digit OTP code.</p>

        <form @submit.prevent="sendOtp" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Username</label>
            <input type="text" x-model="otpForm.username" required class="w-full p-2.5 bg-slate-50 border rounded-lg text-sm">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
            <input type="email" x-model="otpForm.email" required class="w-full p-2.5 bg-slate-50 border rounded-lg text-sm">
          </div>

          <div x-show="otpError" class="p-3 bg-red-50 text-red-700 text-xs rounded-lg" x-text="otpError"></div>

          <button type="submit" :disabled="otpLoading" class="w-full py-2.5 bg-maroon-900 text-white text-sm font-semibold rounded-lg hover:bg-maroon-800 transition">
            <span x-show="!otpLoading">Send Verification Code</span>
            <span x-show="otpLoading" x-cloak>Sending...</span>
          </button>
        </form>
      </div>

      <!-- Step 2: Enter OTP -->
      <div x-show="otpStep === 2" x-cloak>
        <div class="flex items-center justify-between mb-4">
          <h4 class="text-lg font-bold text-slate-900">Enter OTP Code</h4>
          <button @click="otpModal = false" class="text-slate-400 hover:text-slate-600">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>
        <p class="text-xs text-slate-500 mb-5">A 6-digit code has been generated. Please enter it below.</p>

        <form @submit.prevent="verifyOtp" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">6-Digit Code</label>
            <input type="text" x-model="otpForm.otp" maxlength="6" required placeholder="123456" class="w-full p-3 text-center tracking-widest font-mono text-lg bg-slate-50 border rounded-lg">
          </div>

          <div x-show="otpError" class="p-3 bg-red-50 text-red-700 text-xs rounded-lg" x-text="otpError"></div>

          <button type="submit" :disabled="otpLoading" class="w-full py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition">
            <span x-show="!otpLoading">Verify & Continue</span>
            <span x-show="otpLoading" x-cloak>Verifying...</span>
          </button>
        </form>
      </div>

    </div>
  </div>

</div>

<script>
function loginHandler() {
  return {
    form: { username: '', password: '' },
    loading: false,
    errorMessage: '',
    showPass: false,
    otpModal: false,
    otpStep: 1,
    otpLoading: false,
    otpError: '',
    otpForm: { username: '', email: '', otp: '' },

    async submitLogin() {
      this.loading = true;
      this.errorMessage = '';
      try {
        const res = await fetch('<?= Config::BASE_URL ?>/api/login', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.form)
        });
        const data = await res.json();
        if (data.success) {
          window.location.href = '<?= Config::BASE_URL ?>' + (data.redirect || '/dashboard');
        } else {
          this.errorMessage = data.error || 'Authentication failed.';
          this.loading = false;
        }
      } catch (err) {
        this.errorMessage = 'Network or server error. Please try again.';
        this.loading = false;
      }
    },

    async sendOtp() {
      this.otpLoading = true;
      this.otpError = '';
      try {
        const res = await fetch('<?= Config::BASE_URL ?>/api/request-otp', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.otpForm)
        });
        const data = await res.json();
        if (data.success) {
          this.otpStep = 2;
        } else {
          this.otpError = data.error || 'Failed to send OTP.';
        }
      } catch (err) {
        this.otpError = 'Error connecting to server.';
      } finally {
        this.otpLoading = false;
      }
    },

    async verifyOtp() {
      this.otpLoading = true;
      this.otpError = '';
      try {
        const res = await fetch('<?= Config::BASE_URL ?>/api/verify-otp', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ otp: this.otpForm.otp })
        });
        const data = await res.json();
        if (data.success) {
          window.location.href = '<?= Config::BASE_URL ?>/reset-password';
        } else {
          this.otpError = data.error || 'Invalid OTP.';
        }
      } catch (err) {
        this.otpError = 'Error verifying OTP.';
      } finally {
        this.otpLoading = false;
      }
    }
  };
}
</script>
