# QUnit Print Script Dependencies WP-CLI Command

Print dependencies for scripts to be tested and then add into a QUnit HTML test runner file ([see where in the main WP Core QUnit test suite](https://github.com/xwp/wordpress-develop/blob/63755028e8340445ed6062eeba4fa82e3087e433/tests/qunit/index.html#L6-L13), for example).
Uses `wp_print_scripts()` and will include any attached script data or before/after inline scripts. This eliminates a lot of tedious effort
to code up these script tags by hand.

## Examples

Print dependencies for `wp-a11y`:

```
$ wp qunit-print-script-dependencies wp-a11y
<script type='text/javascript' src='http://src.wordpress-develop.dev/wp-includes/js/jquery/jquery.js?ver=1.12.3'></script>
<script type='text/javascript' src='http://src.wordpress-develop.dev/wp-includes/js/jquery/jquery-migrate.js?ver=1.4.0'></script>
<script type='text/javascript' src='http://src.wordpress-develop.dev/wp-includes/js/wp-a11y.js?ver=4.5.2'></script>
```

Print dependencies for `wp-util`:

```
$ wp qunit-print-script-dependencies wp-util
<script type='text/javascript' src='http://src.wordpress-develop.dev/wp-includes/js/underscore.min.js?ver=1.8.3'></script>
<script type='text/javascript' src='http://src.wordpress-develop.dev/wp-includes/js/jquery/jquery.js?ver=1.12.3'></script>
<script type='text/javascript' src='http://src.wordpress-develop.dev/wp-includes/js/jquery/jquery-migrate.js?ver=1.4.0'></script>
<script type='text/javascript'>
/* <![CDATA[ */
var _wpUtilSettings = {"ajax":{"url":"\/wp-admin\/admin-ajax.php"}};
/* ]]> */
</script>
<script type='text/javascript' src='http://src.wordpress-develop.dev/wp-includes/js/wp-util.js?ver=4.5.2'></script>
```

Print dependencies for `wp-util` and `media-models` with overridden base URL.
In this case, it is expected that the script tags would be added to a plugin QUnit test runner
located somewhere like `wp-content/plugins/foo/tests/qunit`:

```
$ wp qunit-print-script-dependencies media-models wplink --base_href=../../../../../
<script type='text/javascript' src='../../../../../wp-includes/js/underscore.min.js?ver=1.8.3'></script>
<script type='text/javascript' src='../../../../../wp-includes/js/jquery/jquery.js?ver=1.12.3'></script>
<script type='text/javascript' src='../../../../../wp-includes/js/jquery/jquery-migrate.js?ver=1.4.0'></script>
<script type='text/javascript' src='../../../../../wp-includes/js/backbone.min.js?ver=1.2.3'></script>
<script type='text/javascript'>
/* <![CDATA[ */
var _wpUtilSettings = {"ajax":{"url":"\/wp-admin\/admin-ajax.php"}};
/* ]]> */
</script>
<script type='text/javascript' src='../../../../../wp-includes/js/wp-util.js?ver=4.5.2'></script>
<script type='text/javascript' src='../../../../../wp-includes/js/wp-backbone.js?ver=4.5.2'></script>
<script type='text/javascript'>
/* <![CDATA[ */
var _wpMediaModelsL10n = {"settings":{"ajaxurl":"\/wp-admin\/admin-ajax.php","post":{"id":0}}};
/* ]]> */
</script>
<script type='text/javascript' src='../../../../../wp-includes/js/media-models.js?ver=4.5.2'></script>
<script type='text/javascript' src='../../../../../wp-includes/js/wp-a11y.js?ver=4.5.2'></script>
<script type='text/javascript'>
/* <![CDATA[ */
var wpLinkL10n = {"title":"Insert\/edit link","update":"Update","save":"Add Link","noTitle":"(no title)","noMatchesFound":"No results found.","linkSelected":"Link selected.","linkInserted":"Link inserted."};
/* ]]> */
</script>
<script type='text/javascript' src='../../../../../wp-includes/js/wplink.js?ver=4.5.2'></script>
```

## Credits

By [XWP](https://make.xwp.co). Licensed GPLv2.
