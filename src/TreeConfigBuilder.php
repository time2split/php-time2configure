<?php
namespace Time2Split\Config;

final class TreeConfigBuilder
{

    private string $delimiter;

    private array $content;

    private ?Interpolator $interpolator;

    private function __construct()
    {
        $this->reset();
    }

    public static function builder(): TreeConfigBuilder
    {
        return new TreeConfigBuilder();
    }

    public static function of(IConfig $config): self
    {
        return self::emptyOf($config)->setContent(\iterator_to_array($config));
    }

    public static function emptyOf(IConfig $config): self
    {
        return (new self())->setDelimiter($config->getKeyDelimiter())
            ->setInterpolator($config->getInterpolator());
    }

    public function reset(): self
    {
        return $this->setDelimiter()
            ->setContent()
            ->setInterpolator();
    }

    public function setInterpolator(?Interpolator $interpolation = null): self
    {
        if (! isset($interpolation))
            $interpolation = Interpolators::null();

        $this->interpolator = $interpolation;
        return $this;
    }

    public function setDelimiter(string $delimiter = ''): self
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    public function setContent(array $content = []): self
    {
        $this->content = $content;
        return $this;
    }

    public function build(): IConfig
    {
        $ret = new _private\TreeConfig( //
        $this->delimiter, //
        $this->interpolator); //

        if (! empty($this->content))
            Configs::merge($ret, $this->content);

        return $ret;
    }
}