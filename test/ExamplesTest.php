<?php

declare(strict_types=1);

namespace KynxTest\GraphQLite;

use Generator;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function basename;
use function escapeshellarg;
use function exec;
use function glob;
use function implode;

#[CoversNothing]
final class ExamplesTest extends TestCase
{
    use ConnectionTrait;

    private const string EXAMPLES_DIR = __DIR__ . '/../examples';

    #[DataProvider('exampleProvider')]
    public function testExample(string $path): void
    {
        $extensionPath = $this->getExtensionPath();

        $output = [];
        $code   = null;
        exec(
            "GRAPHQLITE_EXTENSION_PATH='$extensionPath' php " . escapeshellarg($path) . ' 2>&1',
            $output,
            $code
        );

        $output = implode("\n", $output);

        self::assertSame(0, $code, "Example returned non-zero exit code ($code):\n$output");
    }

    /**
     * @return Generator<string, array{string}>
     */
    public static function exampleProvider(): Generator
    {
        foreach (glob(self::EXAMPLES_DIR . '/*.php') ?: [] as $file) {
            yield basename($file) => [$file];
        }
    }
}
