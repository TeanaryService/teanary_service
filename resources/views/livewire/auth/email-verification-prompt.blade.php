<div class="max-w-xl mx-auto py-16">
    <h2 class="text-2xl font-bold mb-4">验证你的邮箱</h2>

    <p class="mb-4 text-gray-600">我们已向你的邮箱发送了一封验证链接。如果你没有收到，可以点击下面的按钮重新发送。</p>

    @if ($resent)
        <div class="text-green-600 mb-4">新的验证链接已发送到你的邮箱。</div>
    @endif

    <button wire:click="sendVerificationEmail"
        class="bg-teal-600 text-white px-6 py-2 rounded hover:bg-teal-700 transition-colors">
        重新发送验证邮件
    </button>
</div>
