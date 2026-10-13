<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Writes a small, standards-compliant XLSX workbook without building a
 * PhpSpreadsheet object for every cell. This keeps large administrator
 * exports bounded by disk space instead of the PHP-FPM memory limit.
 */
final class StreamingXlsxExporter
{
    /**
     * @param iterable<mixed> $rows
     * @param array<int, mixed> $headings
     * @param callable(mixed): array<int, mixed> $map
     */
    public static function create(iterable $rows, array $headings, callable $map): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('The PHP ZIP extension is required to create an Excel export.');
        }

        $workingDirectory = self::makeWorkingDirectory();
        $sheetPath = $workingDirectory . DIRECTORY_SEPARATOR . 'sheet1.xml';
        $workbookPath = $workingDirectory . DIRECTORY_SEPARATOR . 'workbook.xml';
        $workbookRelsPath = $workingDirectory . DIRECTORY_SEPARATOR . 'workbook.xml.rels';
        $stylesPath = $workingDirectory . DIRECTORY_SEPARATOR . 'styles.xml';
        $contentTypesPath = $workingDirectory . DIRECTORY_SEPARATOR . '[Content_Types].xml';
        $rootRelsPath = $workingDirectory . DIRECTORY_SEPARATOR . 'root.rels';
        $corePath = $workingDirectory . DIRECTORY_SEPARATOR . 'core.xml';
        $appPath = $workingDirectory . DIRECTORY_SEPARATOR . 'app.xml';
        $outputPath = $workingDirectory . '.xlsx';

        try {
            $sheet = fopen($sheetPath, 'wb');
            if ($sheet === false) {
                throw new RuntimeException('Unable to create the temporary result worksheet.');
            }

            fwrite($sheet, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>');
            fwrite($sheet, '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">');
            fwrite($sheet, '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>');
            fwrite($sheet, '<sheetFormatPr defaultRowHeight="15"/>');
            fwrite($sheet, '<cols>');
            self::writeColumnDefinitions($sheet, count($headings));
            fwrite($sheet, '</cols><sheetData>');

            self::writeRow($sheet, $headings, 1, true);
            $lastRow = 1;

            foreach ($rows as $row) {
                $lastRow++;
                self::writeRow($sheet, $map($row), $lastRow, false);

                if (($lastRow % 1000) === 0) {
                    fflush($sheet);
                }
            }

            $lastColumn = self::columnName(count($headings));
            fwrite($sheet, '</sheetData><autoFilter ref="A1:' . $lastColumn . $lastRow . '"/><pageMargins left="0.25" right="0.25" top="0.5" bottom="0.5" header="0.3" footer="0.3"/></worksheet>');
            fclose($sheet);

            file_put_contents($workbookPath, self::workbookXml());
            file_put_contents($workbookRelsPath, self::workbookRelationshipsXml());
            file_put_contents($stylesPath, self::stylesXml());
            file_put_contents($contentTypesPath, self::contentTypesXml());
            file_put_contents($rootRelsPath, self::rootRelationshipsXml());
            file_put_contents($corePath, self::corePropertiesXml());
            file_put_contents($appPath, self::appPropertiesXml());

            $zip = new ZipArchive();
            $opened = $zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            if ($opened !== true) {
                throw new RuntimeException('Unable to create the Excel workbook archive.');
            }

            $zip->addFile($contentTypesPath, '[Content_Types].xml');
            $zip->addFile($rootRelsPath, '_rels/.rels');
            $zip->addFile($corePath, 'docProps/core.xml');
            $zip->addFile($appPath, 'docProps/app.xml');
            $zip->addFile($workbookPath, 'xl/workbook.xml');
            $zip->addFile($workbookRelsPath, 'xl/_rels/workbook.xml.rels');
            $zip->addFile($stylesPath, 'xl/styles.xml');
            $zip->addFile($sheetPath, 'xl/worksheets/sheet1.xml');

            if (!$zip->close()) {
                throw new RuntimeException('Unable to finish the Excel workbook archive.');
            }

            self::removeDirectory($workingDirectory);

            return $outputPath;
        } catch (\Throwable $exception) {
            if (isset($sheet) && is_resource($sheet)) {
                fclose($sheet);
            }
            self::removeDirectory($workingDirectory);
            @unlink($outputPath);
            throw $exception;
        }
    }

    /**
     * @param resource $handle
     * @param array<int, mixed> $values
     */
    private static function writeRow($handle, array $values, int $rowNumber, bool $heading): void
    {
        fwrite($handle, '<row r="' . $rowNumber . '">');

        foreach (array_values($values) as $index => $value) {
            $coordinate = self::columnName($index + 1) . $rowNumber;
            $style = $heading ? ' s="1"' : '';

            if (!$heading && is_numeric($value) && trim((string) $value) !== '') {
                fwrite($handle, '<c r="' . $coordinate . '"' . $style . '><v>' . self::xml((string) $value) . '</v></c>');
                continue;
            }

            fwrite($handle, '<c r="' . $coordinate . '"' . $style . ' t="inlineStr"><is><t xml:space="preserve">' . self::xml((string) ($value ?? '')) . '</t></is></c>');
        }

        fwrite($handle, '</row>');
    }

    private static function columnName(int $column): string
    {
        $name = '';
        while ($column > 0) {
            $remainder = ($column - 1) % 26;
            $name = chr(65 + $remainder) . $name;
            $column = intdiv($column - 1, 26);
        }

        return $name;
    }

    /**
     * Keep the most useful context columns readable in Excel while allowing
     * the writer to support any heading count without changing the sheet XML.
     *
     * @param resource $handle
     */
    private static function writeColumnDefinitions($handle, int $columnCount): void
    {
        $widths = [
            8, 30, 30, 12, 14, 14, 16, 42, 20, 10, 10, 10, 10, 16,
        ];

        for ($column = 1; $column <= $columnCount; $column++) {
            $width = $widths[$column - 1] ?? 18;
            fwrite($handle, '<col min="' . $column . '" max="' . $column . '" width="' . $width . '" customWidth="1"/>');
        }
    }

    private static function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function makeWorkingDirectory(): string
    {
        $preferred = DIRECTORY_SEPARATOR === '/' ? '/mnt/storage/umstad-results-export' : null;
        $candidates = array_filter([
            \env('RESULTS_EXPORT_TEMP_PATH'),
            $preferred,
            \storage_path('framework/cache/results-export'),
        ]);

        foreach ($candidates as $directory) {
            if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
                continue;
            }

            if (!is_writable($directory)) {
                continue;
            }

            $seed = tempnam($directory, 'results-export-');
            if ($seed === false) {
                continue;
            }

            @unlink($seed);
            if (@mkdir($seed, 0775, true)) {
                return $seed;
            }
        }

        throw new RuntimeException('Unable to create temporary storage for the Excel export.');
    }

    private static function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        foreach (new \DirectoryIterator($directory) as $file) {
            if ($file->isDot()) {
                continue;
            }

            if ($file->isDir()) {
                self::removeDirectory($file->getPathname());
            } else {
                @unlink($file->getPathname());
            }
        }

        @rmdir($directory);
    }

    private static function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . '</Types>';
    }

    private static function rootRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private static function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Approved Results" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private static function workbookRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private static function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="0"/><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="1769AA"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="2"><border/><border><left style="thin"/><right style="thin"/><top style="thin"/><bottom style="thin"/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf></cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private static function corePropertiesXml(): string
    {
        $now = gmdate('Y-m-d\TH:i:s\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:creator>University of Maiduguri Portal</dc:creator><dcterms:created xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:created>'
            . '</cp:coreProperties>';
    }

    private static function appPropertiesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>University of Maiduguri Portal</Application><DocSecurity>0</DocSecurity><ScaleCrop>false</ScaleCrop>'
            . '</Properties>';
    }
}
