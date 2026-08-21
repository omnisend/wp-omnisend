<?php
/**
 * Regenerates the SDK surface baseline used by tests/Omnisend/SDK/SdkSurfaceTest.php.
 *
 * Run it only when a change to the public SDK surface is deliberate and approved:
 *
 *   php tests/tools/dump-sdk-surface.php > tests/Omnisend/SDK/sdk-surface-baseline.php
 */

require_once __DIR__ . '/../dependencies/dependencies.php';
require_once __DIR__ . '/../Omnisend/SDK/SdkSurface.php';

use Omnisend\Tests\SDK\SdkSurface;

foreach (SdkSurface::sdk_class_names() as $class_name) {
    class_exists($class_name) || interface_exists($class_name);
}

echo "<?php\n";
echo "/**\n";
echo " * Baseline of the public SDK surface. Regenerate with tests/tools/dump-sdk-surface.php\n";
echo " * only when the change to the surface is deliberate and approved.\n";
echo " */\n\n";
echo 'return ' . var_export(SdkSurface::collect(), true) . ";\n";
