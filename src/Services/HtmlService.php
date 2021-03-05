<?php

namespace App\Services;

use DOMDocument;

class HtmlService {

    public static function clean($html)
    {
        if (empty($html)) {
            return $html;
        }

        //return self::tidy($html);
        return self::dom(self::tidy($html));
    }

    public static function tidy($html)
    {
        try {
            $tidy = new \Tidy();
            $options = [
                'output-html' => true,
                'show-errors' => 0,
                'wrap' => 0,
                'doctype' => 'omit',
                'drop-empty-elements' => false,
                'drop-empty-paras' => false,
            ];

            if (strpos($html, '<html') === false) {
                $options['show-body-only'] = true;
            }

            return $tidy->repairString($html, $options, 'utf8');
            // $tidy->parseString($html, $options, 'utf8');
            // $tidy->cleanRepair();
            // return $tidy;
        }
        catch (\Exception $e) {
            return $html;
        }
    }

    public static function dom($html) 
    {
        try {
            $dom = new DOMDocument('1.0', 'utf-8');
            $out = [];

            if (strpos($html, '<?xml encoding="utf-8"') !== false) {
                $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            } else {
                $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            }

            if ($dom->hasChildNodes()) {
                foreach ($dom->childNodes as $node) {
                    $out[] = $dom->saveHTML($node);
                }
            }

            return str_replace(['<?xml encoding="utf-8" ?>', '<?xml version="1.0" encoding="utf-8"?>'], '', implode('', $out));
        } catch (\Exception $e) {
            return $html;
        }
    }
}