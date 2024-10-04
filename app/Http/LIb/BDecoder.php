<?php

namespace App\Http\LIb;

final class BDecoder
{
    private string $content;
    private int $pointer = 0;
    public array $result = [];

    function __construct($filepath)
    {
        $this->content = @file_get_contents($filepath);

        if (!$this->content) {
            $this->throwException('File does not exist!');
        } else {
            if (!isset($this->content)) {
                $this->throwException('Error opening file!');
            } else {
                $this->result = $this->processElement();
            }
        }
        unset($this->content);
    }

    function __destruct()
    {
        unset($this->content);
        unset($this->result);
    }

    private function throwException($error = 'error parsing file')
    {
        $this->result = array();
        $this->result['error'] = $error;
    }

    private function processElement()
    {
        switch ($this->content[$this->pointer]) {
            case 'd':
                return $this->processDictionary();
            case 'l':
                return $this->processList();
            case 'i':
                return $this->processInteger();
            default:
                if (is_numeric($this->content[$this->pointer])) {
                    return $this->processString();
                } else {
                    $this->throwException('Unknown BEncode element');
                }
                break;
        }

        return null;
    }

    private function processDictionary()
    {
        if (!$this->isOfType('d'))
            $this->throwException();

        $res = [];
        $this->pointer++;

        while (!$this->isOfType('e')) {
            $elemkey = $this->processString();

            switch ($this->content[$this->pointer]) {
                case 'd':
                    $res[$elemkey] = $this->processDictionary();
                    break;
                case 'l':
                    $res[$elemkey] = $this->processList();
                    break;
                case 'i':
                    $res[$elemkey] = $this->processInteger();
                    break;
                default:
                    if (is_numeric($this->content[$this->pointer])) {
                        $res[$elemkey] = $this->processString();
                    } else {
                        $this->throwException('Unknown BEncode element!');
                    }
                    break;
            }
        }

        $this->pointer++;
        return $res;
    }

    private function processList()
    {
        if (!$this->isOfType('l'))
            $this->throwException();

        $res = array();
        $this->pointer++;

        while (!$this->isOfType('e'))
            $res[] = $this->processElement();

        $this->pointer++;
        return $res;
    }

    private function processInteger()
    {
        if (!$this->isOfType('e'))
            $this->throwException();

        $this->pointer++;

        $delim_pos = strpos($this->content, 'e', $this->pointer);
        $integer = substr($this->content, $this->pointer, $delim_pos - $this->pointer);
        if (($integer == '-0') || ((str_starts_with($integer, '0')) && (strlen($integer) > 1)))
            $this->throwException();

        $integer = abs(floatval($integer));
        $this->pointer = $delim_pos + 1;
        return $integer;
    }

    private function processString()
    {
        if (!is_numeric($this->content[$this->pointer])) {
            $this->throwException();
        }

        $delim_pos = strpos($this->content, ':', $this->pointer);
        $elem_len = intval(substr($this->content, $this->pointer, $delim_pos - $this->pointer));
        $this->pointer = $delim_pos + 1;

        $elem_name = substr($this->content, $this->pointer, $elem_len);

        $this->pointer += $elem_len;
        return $elem_name;
    }

    private function isOfType($type)
    {
        return ($this->content[$this->pointer] == $type);
    }

    public function getInfoHash($data)
    {
        $bencodedInfo = $this->encodeHash($data);
        $infoHash = sha1($bencodedInfo, true);
        return bin2hex($infoHash);
    }

    public function encodeHash($data)
    {
        if (is_array($data)) {
            $result = 'd';
            foreach ($data as $key => $value) {
                $result .= $this->getInfoHash($key) . $this->getInfoHash($value);
            }
            return $result . 'e';
        } elseif (is_int($data)) {
            return 'i' . $data . 'e';
        } elseif (is_string($data)) {
            return strlen($data) . ':' . $data;
        }

        return '';
    }
}
