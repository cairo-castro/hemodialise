@if(auth()->check() && in_array(auth()->user()->role, ['admin', 'gestor', 'coordenador', 'supervisor']))
<div class="flex items-center space-x-3 mr-4">
    <label for="interface-switcher" class="text-sm font-medium text-gray-700 dark:text-gray-300">
        Interface:
    </label>
    <select
        id="interface-switcher"
        onchange="switchInterface(this.value)"
        class="block w-32 text-sm border-gray-300 dark:border-gray-600 rounded-md shadow-sm
               focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
    >
        <option value="admin" {{ request()->is('admin*') ? 'selected' : '' }}>🛠️ Admin</option>
        <option value="desktop" {{ request()->is('desktop*') ? 'selected' : '' }}>🖥️ Desktop</option>
        <option value="mobile" {{ request()->is('mobile*') ? 'selected' : '' }}>📱 Mobile</option>
    </select>
</div>

<script>
function switchInterface(interface) {
    const urls = {
        'admin': '/admin',
        'desktop': '/desktop/preline',
        'mobile': '/mobile/ionic'
    };

    if (urls[interface]) {
        window.location.href = urls[interface];
    }
}
</script>
@endif