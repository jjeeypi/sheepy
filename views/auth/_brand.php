<a class="auth-brand<?= isset($brandClass) && $brandClass !== '' ? ' ' . $escape($brandClass) : '' ?>" href="<?= $escape($brandUrl) ?>" aria-label="Sheepy sign in">
    <span class="auth-brand-name">Sheepy</span>
    <svg class="auth-brand-mark" aria-hidden="true" viewBox="0 0 34 24" fill="none">
        <path d="M2 6.5h21.5v5.25H28v4.5h3.5" />
        <path d="M23.5 11.75v7.75M28 16.25v3.25" />
        <circle cx="31.5" cy="19.5" r="1.5" />
    </svg>
</a>
