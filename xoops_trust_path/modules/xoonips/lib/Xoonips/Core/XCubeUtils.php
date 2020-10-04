<?php

namespace Xoonips\Core;

/**
 * xcube utils compatibility class.
 */
class XCubeUtils
{
    /**
     * format string.
     *
     * @param string $message
     * @param array  $params
     *
     * @return string
     */
    public static function formatString(string $message, array $params): string
    {
        $vars = $params;
        if (is_array($params)) {
            $vars = $params;
        } else {
            $vars = func_get_args();
            array_shift($vars);
        }
        for ($i = 0; $i < count($vars); ++$i) {
            $message = str_replace('{'.$i.'}', $vars[$i], $message);
            $message = str_replace('{'.$i.':ucFirst}', ucfirst($vars[$i]), $message);
            $message = str_replace('{'.$i.':toLower}', strtolower($vars[$i]), $message);
            $message = str_replace('{'.$i.':toUpper}', strtoupper($vars[$i]), $message);
        }

        return $message;
    }
}
