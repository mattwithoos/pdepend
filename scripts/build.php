<?php

$root = realpath(__DIR__ . '/../') . '/';

$archiveName = 'pdepend.phar';
$version = parse_ini_file($root . 'build.properties')['project.version'] ?? '@package_version@';

echo 'PDepend ', $version, PHP_EOL, PHP_EOL;

$phar = new Phar($archiveName);
$phar->buildFromDirectory($root, '/^' . preg_quote($root, '/') . 'src/');
$phar->buildFromDirectory($root, '/^' . preg_quote($root, '/') . 'vendor(?!.*\/symfony\/.*\/Test\/).*$/');

$patchList = [
    'src/TextUI/Command.php',
    'src/Report/Summary/Xml.php',
    'src/Report/Dependencies/Xml.php',
];
foreach ($patchList as $filePath) {
    $content = file_get_contents($root . $filePath);
    if (!$content) {
        continue;
    }
    $fileContent = str_replace('@package_version@', $version, $content);
    $phar->addFromString($filePath, $fileContent);
}

// Set a custom stub
$customStubContent = file_get_contents($root . 'conf/phar_bootstrap.stub');
if (!$customStubContent) {
    throw new Exception('Unable to load bootstrap stub');
}
$customStubContent = str_replace('${archive.alias}', $archiveName, $customStubContent);
$phar->setStub($customStubContent);
