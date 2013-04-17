<?php

namespace Translator\Import\Source;

class PortableObject implements SourceInterface
{
    private $translations = array();

    public function getIterator()
    {
        return new \ArrayIterator($this->translations);
    }

    /**
     * @param string $filePath
     * @return self
     */
    public function select($filePath)
    {
        $this->translations = array();

        $f = fopen($filePath, 'r');

        $buffer = '';
        while (($str = fgets($f)) !== false) {
            if (strlen(trim($str))) {
                $buffer .= $str;
            } else {
                $this->translations = array_merge(
                    $this->translations,
                    self::interpret($buffer)
                );
                $buffer = '';
            }
        }
        $this->translations = array_merge(
            $this->translations,
            self::interpret($buffer)
        );
    }

//----------------------------------------------------------------------------------------------------------------------

    private static function interpret($buffer)
    {
        //echo "\n" . $buffer . "\n\n";

        list($buffer, $msgid) = self::readMsgId($buffer);
        list($buffer, $msgstr) = self::readMsgStr($buffer);
        list($buffer, $msgctxt) = self::readMsgContext($buffer);

        return $msgid ? array($msgctxt ? $msgctxt . ':' . $msgid : $msgid => $msgstr) : array();
    }

    private static function readMsgId($buffer)
    {
        $lines = explode("\n", $buffer);
        foreach ($lines as $idx => $line) {
            if (preg_match('/\s*msgid\s+"(.*)"\s*$/i', $line, $matches)) {
                unset($lines[$idx]);
                return array(join("\n", $lines), stripslashes($matches[1]));
            }
        }
        return array($buffer, null);
    }

    private static function readMsgStr($buffer)
    {
        $lines = explode("\n", $buffer);

        // skip to msgstr definition
        $msgStartIndex = null;
        foreach ($lines as $idx => $line) {
            if (stripos($line, 'msgstr "') !== false) {
                $msgStartIndex = $idx;
                break;
            }
        }
        if (!is_null($msgStartIndex)) {
            // read first line
            $string = preg_match('/\s*msgstr\s+"(.*)"\s*$/i', $lines[$msgStartIndex], $matches) ? stripslashes($matches[1]) : "";
            unset($lines[$msgStartIndex]);

            // read and concatenate the rest
            for($i = $msgStartIndex + 1; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                if (!strlen($line) || (strpos($line, '"') !== 0)) {
                    break;
                } else {
                    $string .= stripslashes(str_replace('\\n', "\n", trim($line, '"')));
                }
            }
        } else {
            $string = null;
        }

        return array(join("\n", $lines), $string);
    }

    private static function readMsgContext($buffer)
    {
        $lines = explode("\n", $buffer);
        foreach ($lines as $idx => $line) {
            if (preg_match('/\s*msgctxt\s+"(.*)"\s*$/i', $line, $matches)) {
                unset($lines[$idx]);
                return array(join("\n", $lines), stripslashes($matches[1]));
            }
        }

        return array(join("\n", $lines), null);
    }
}