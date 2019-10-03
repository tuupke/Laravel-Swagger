<?php namespace Tuupke\Swagger;

class Util {

    public static function includeResolver(array $access): array {
        $excludesArray = [];
        $includes = [];

        foreach ($access as $item) {
            $type = "excludes";
            $value = null;

            if (is_array($item)) {
                $type = @$item["type"] ?? "excludes";
                $value = @$item["value"];

                if (is_null($value)) {
                    continue;
                }
            } else {
                $value = $item;
            }

            $val = self::valueToFileOrFolderArray($value);

            switch ($type) {
                case "includes":
                case "include":
                case "in":
                    $includes = array_merge($includes, $val, [$value]);
                    break;

                case "excludes":
                case "exclude":
                case "ex":
                    $excludesArray = array_merge($excludesArray, $val);
                    break;
            }
        }

        return array_diff(array_unique($excludesArray), $includes);
    }

    public static function valueToFileOrFolderArray(string $value): array {
        if (!\file_exists($value)) {
            return [];
        }

        if (!is_dir($value)) {
            return [$value];
        }

        $val = scandir($value);

        array_shift($val);
        array_shift($val);

        return array_map(function ($item) use ($value) {
            return $value . '/' . $item;
        }, $val);
    }

    public static function ensureDir($path) {
        // We strip away the first /
        $pathParts = explode("/", ltrim($path, "/"));

        // Strip the last part, when it is not a dir (suffix == "/"
        $file = null;
        if ($path[strlen($path) - 1] != "/") {
            $file = array_pop($pathParts);
        }

        $split = [];
        // Remove "", and "." and handle ".."
        foreach ($pathParts as $part) {
            switch ($part) {
                case "":
                case ".":
                    continue;
                case "..":
                    array_pop($split);
                    continue;
                default:
                    array_push($split, $part);
            }
        }

        $cp = "";
        foreach ($split as $s) {
            if (!is_dir($cp .= "/$s")) {
                mkdir("$cp");
            }
        }

        if (!is_null($file)) {
            $cp .= "/$file";
        }

        return $cp;
    }
}
