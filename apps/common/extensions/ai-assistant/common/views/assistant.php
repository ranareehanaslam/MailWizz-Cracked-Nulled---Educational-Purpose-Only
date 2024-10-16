<?php declare(strict_types=1);
/** @var Controller $controller */
$controller = controller();

$apiBaseUrl             = (string)$controller->getData('apiBaseUrl');
$siteName               = (string)$controller->getData('siteName');
$hasSecretKey           = (bool)$controller->getData('hasSecretKey');
$canManageOpenAiAccount = (bool)$controller->getData('canManageOpenAiAccount');
$moduleIndexSrc         = (string)$controller->getData('moduleIndexSrc');
$polyFillsLegacySrc     = (string)$controller->getData('polyFillsLegacySrc');
$indexLegacySrc         = (string)$controller->getData('indexLegacySrc');
?>

<div
    id="ai-assistant-root"
    data-api-base-url="<?php echo html_encode($apiBaseUrl); ?>"
    data-site-name="<?php echo html_encode($siteName); ?>"
    data-csrf-token-name="<?php echo request()->csrfTokenName; ?>"
    data-csrf-token-value="<?php echo request()->getCsrfToken(); ?>"
    data-has-secret-key="<?php echo $hasSecretKey; ?>"
    data-can-manage-open-ai-account="<?php echo $canManageOpenAiAccount; ?>"
>

</div>
<script type="module" crossorigin src="<?php echo $moduleIndexSrc; ?>"></script>

<script type="module">import.meta.url;
    import("_").catch(() => 1);

    async function* g() {
    };window.__vite_is_modern_browser = true;</script>
<script type="module">!function () {
        if (window.__vite_is_modern_browser) return;
        console.warn("vite: loading legacy chunks, syntax error above and the same error below should be ignored");
        var e = document.getElementById("vite-legacy-polyfill"), n = document.createElement("script");
        n.src = e.src, n.onload = function () {
            System.import(document.getElementById('vite-legacy-entry').getAttribute('data-src'))
        }, document.body.appendChild(n)
    }();</script>

<script nomodule>!function () {
        var e = document, t = e.createElement("script");
        if (!("noModule" in t) && "onbeforeload" in t) {
            var n = !1;
            e.addEventListener("beforeload", (function (e) {
                if (e.target === t) n = !0; else if (!e.target.hasAttribute("nomodule") || !n) return;
                e.preventDefault()
            }), !0), t.type = "module", t.src = ".", e.head.appendChild(t), t.remove()
        }
    }();</script>
<script nomodule crossorigin id="vite-legacy-polyfill" src="<?php echo $polyFillsLegacySrc; ?>"></script>
<script nomodule crossorigin id="vite-legacy-entry"
        data-src="<?php echo $indexLegacySrc; ?>">System.import(document.getElementById('vite-legacy-entry').getAttribute('data-src'))</script>
