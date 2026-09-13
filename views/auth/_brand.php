<a class="auth-brand<?= isset($brandClass) && $brandClass !== '' ? ' ' . $escape($brandClass) : '' ?>" href="<?= $escape($brandUrl) ?>" aria-label="Sheepy sign in">
    <img
        class="auth-brand-logo"
        src="<?= $escape($logoUrl ?? '/assets/images/logo/logo.svg') ?>"
        alt="Sheepy"
        width="416"
        height="245"
        decoding="async"
    >
</a>
