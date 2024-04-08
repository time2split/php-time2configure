<?php

namespace Time2Split\Config\_private;

use Time2Split\Config\_private\Value\Getters;

/**
 * Reader able to detect {offset} interpolation tokens in a text string.
 */
final class InterpolationReader
{

    /**
     * @var resource
     */
    private mixed $fp;

    public function for(mixed $text): \Time2Split\Help\Optional
    {
        $this->fp = \Time2Split\Help\Streams::readableStream($text);
        $getters = [];

        do {
            $update = false;
            $s = $this->nextString();

            if ($s !== '') {
                $getters[] = $s;
                $update = true;
            }
            $get = $this->nextGetter();

            if (!empty($get)) {
                $getters[] = Getters::fromClosure($get);
                $update = true;
            }
        } while ($update);

        $c = \count($getters);
        if ($c === 0)
            return \Time2Split\Help\Optional::empty();

        if ($c === 1) {
            $v = $getters[0];

            if (\is_string($v))
                return \Time2Split\Help\Optional::empty();
        } else

            $v = Getters::fromClosure(function ($subject) use ($getters) {
                return Getters::map($getters, $subject);
            });

        return \Time2Split\Help\Optional::of($v);
    }

    private function nextString(): string
    {
        return \Time2Split\Help\Streams::streamGetChars($this->fp, fn ($c) => $c !== '$');
    }

    private function skipSpaces(): void
    {
        \Time2Split\Help\Streams::streamSkipChars($this->fp, \ctype_space(...));
    }

    // private function ungetc(): bool
    // {
    //     return \Time2Split\Help\Streams::streamUngetc($this->fp);
    // }

    private function nextChar(): string|false
    {
        $this->skipSpaces();
        return \fgetc($this->fp);
    }

    private function nextConfigKey(): string
    {
        $this->skipSpaces();
        return \Time2Split\Help\Streams::streamGetChars($this->fp, fn ($c) => \ctype_alnum($c) || $c === '.' || $c === '_');
    }

    // private function nextWord(): string
    // {
    //     $this->skipSpaces();
    //     return \Time2Split\Help\Streams::streamGetChars($this->fp, fn ($c) => \ctype_alnum($c) || $c === '=' || $c === '.');
    // }

    private function nextGetter(): ?\Closure
    {
        $interpolation = null;
        $states = [
            0
        ];

        while (true) {
            $state = \array_pop($states);

            switch ($state) {
                case -1:
                    return null;

                case /* Init */ 0:
                    $c = $this->nextChar();

                    if ($c === '$')
                        \array_push($states, 1);
                    else
                        \array_push($states, -1);
                    break;

                case /* Interpolation start */ 1:
                    $c = $this->nextChar();

                    if ($c === '{') {
                        \array_push($states, 2);
                        \array_push($states, 20);
                        // \array_push($states, 10);
                    } else
                        \array_push($states, -1);
                    break;

                case /* Interpolation end */ 2:
                    $c = $this->nextChar();

                    if ($c === '}')
                        return $interpolation;
                    else
                        \array_push($states, -1);
                    break;

                case /* Read a key */ 20:
                    $key = $this->nextConfigKey();
                    $interpolation = Interpreters::getArrayValueFunction($key);
                    break;
            }
        }
    }
}
