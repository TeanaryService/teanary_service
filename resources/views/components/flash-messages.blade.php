<div>
    @if (session('success'))
        <x-alert type="success" :message="session('success')" />
    @endif

    @if (session('error'))
        <x-alert type="error" :message="session('error')" />
    @endif
    @if (session('warning'))
        <x-alert type="warning" :message="session('warning')" />
    @endif
    @if (session('info'))
        <x-alert type="info" :message="session('info')" />
    @endif
</div>
