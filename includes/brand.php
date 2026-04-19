<?php
declare(strict_types=1);

/**
 * Genacom marketing CDN. The legacy *.svg logo path returns 403 when hotlinked;
 * the live genacom.com header uses inline SVG; this PNG wordmark loads reliably.
 */
function genacom_logo_url(): string
{
    return 'https://cdn.prod.website-files.com/698569e723cb64d9d28f0a78/698569e723cb64d9d28f0af9_genacom-wc.png';
}

function genacom_favicon_url(): string
{
    return 'https://cdn.prod.website-files.com/698569e723cb64d9d28f0a78/698569e723cb64d9d28f0af8_genacom-fav.png';
}
