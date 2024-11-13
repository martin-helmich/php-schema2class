<?php

namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Writer\WriterInterface;
use Laminas\Code\Generator\EnumGenerator\EnumGenerator;
use Laminas\Code\Generator\FileGenerator;

class SchemaToEnum
{
    private WriterInterface $writer;

    public function __construct(WriterInterface $writer)
    {
        $this->writer = $writer;
    }

    public function schemaToEnum(GeneratorRequest $req): void
    {
        if (!$req->isAtLeastPHP("8.1")) {
            throw new GeneratorException("cannot generate enum classes for PHP versions < 8.1");
        }

        /** @var array<non-empty-string, string|int> $cases */
        $cases = [];
        foreach ($req->getSchema()["enum"] as $case) {
            if (!is_string($case) && !is_int($case)) {
                throw new GeneratorException("cannot generate enum classes for non-string/non-int enum values");
            }

            $name  = self::enumCaseName($case);
            $value = $case;

            $cases[$name] = $value;
        }

        $cases = self::makeCaseNamesConsistent($cases);

        $type     = $req->getSchema()["type"] === "string" ? "string" : "int";
        $enumName = $req->getTargetNamespace() . "\\" . $req->getTargetClass();
        $enum     = EnumGenerator::withConfig([
            "name"        => $enumName,
            "backedCases" => [
                "type"  => $type,
                "cases" => $cases,
            ],
        ]);

        $req->onEnumCreated($enumName, $enum);

        $filename = $req->getTargetDirectory() . '/' . $req->getTargetClass() . '.php';
        $file     = new FileGenerator();
        $file->setBody($enum->generate());

        $req->onFileCreated($filename, $file);

        // No strict typings for enums, because Psalm shits itself in that case.
        // $file->setDeclares([DeclareStatement::strictTypes(1)]);

        $content = $file->generate();

        // Do some corrections because the Zend code generation library is stupid.
        $content = preg_replace('/ : \\\\self/', ' : self', $content);
        $content = preg_replace('/\\\\' . preg_quote($req->getTargetNamespace()) . '\\\\/', '', $content);

        $this->writer->writeFile($filename, $content);
    }


    /**
     * @param array<non-empty-string, string|int> $cases
     * @return array<non-empty-string, string|int>
     */
    private static function makeCaseNamesConsistent(array $cases): array
    {
        $hasValuePrefix = false;

        foreach ($cases as $name => $value) {
            if (str_starts_with($name, "VALUE_")) {
                $hasValuePrefix = true;
                break;
            }
        }

        if (!$hasValuePrefix) {
            return $cases;
        }

        $newCases = [];
        foreach ($cases as $name => $value) {
            if (str_starts_with($name, "VALUE_")) {
                $newCases[$name] = $value;
            } else {
                $newCases["VALUE_$name"] = $value;
            }
        }

        return $newCases;
    }


    /**
     * @param string|int $value
     * @return non-empty-string
     */
    public static function enumCaseName(string|int $value): string
    {
        if (is_int($value)) {
            return "VALUE_$value";
        }

        $value = static::enumCaseNameString($value);

        if (is_numeric($value[0])) {
            return "VALUE_$value";
        }

        if ($value === "") {
            return "EMPTY";
        }

        return $value;
    }

    /**
     * @param string $value
     * @return string
     */
    private static function enumCaseNameString(string $value): string
    {
        return preg_replace('/[^a-zA-Z0-9]/', '', $value);
    }

}
