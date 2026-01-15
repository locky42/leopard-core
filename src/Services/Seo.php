<?php

namespace Leopard\Core\Services;

/**
 * SEO Service
 *
 * This service manages SEO-related metadata for web pages, including
 * meta tags, Open Graph tags, Twitter cards, titles, descriptions,
 * canonical URLs, keywords, and robots directives.
 */
class Seo
{
    /**
     * @var array<string, string>
     */
    protected array $metaTags = [];

    /**
     * @var array<string, string>
     */
    protected array $openGraphTags = [];

    /**
     * @var array<string, string>
     */
    protected array $twitterCards = [];

    /**
     * @var string|null
     */
    protected ?string $title = null;

    /**
     * @var string|null
     */
    protected ?string $description = null;

    /**
     * @var string|null
     */
    protected ?string $canonicalUrl = null;

    /**
     * @var array<int, string>
     */
    protected array $keywords = [];

    /**
     * @var string|null
     */
    protected ?string $robots = null;

    /**
     * @var string|null
     */
    protected ?string $charset = null;

    /**
     * @return array<string, string>
     */
    public function getMetaTags(): array
    {
        return $this->metaTags;
    }

    /**
     * @param string $name
     * @param string $content
     */
    public function addMetaTag(string $name, string $content): void
    {
        $this->metaTags[$name] = $content;
    }

    /**
     * @param string $name
     */
    public function removeMetaTag(string $name): void
    {
        unset($this->metaTags[$name]);
    }

    public function setMetaTags(array $metaTags): void
    {
        $this->metaTags = $metaTags;
    }

    public function getOpenGraphTags(): array
    {
        return $this->openGraphTags;
    }

    public function addOpenGraphTag(string $property, string $content): void
    {
        $this->openGraphTags[$property] = $content;
    }

    public function removeOpenGraphTag(string $property): void
    {
        unset($this->openGraphTags[$property]);
    }

    public function setOpenGraphTags(array $openGraphTags): void
    {
        $this->openGraphTags = $openGraphTags;
    }

    public function getTwitterCards(): array
    {
        return $this->twitterCards;
    }

    public function addTwitterCard(string $name, string $content): void
    {
        $this->twitterCards[$name] = $content;
    }

    public function removeTwitterCard(string $name): void
    {
        unset($this->twitterCards[$name]);
    }

    public function setTwitterCards(array $twitterCards): void
    {
        $this->twitterCards = $twitterCards;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getCanonicalUrl(): ?string
    {
        return $this->canonicalUrl;
    }

    public function setCanonicalUrl(string $canonicalUrl): void
    {
        $this->canonicalUrl = $canonicalUrl;
    }

    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function setKeyword(string $keyword): void
    {
        if (!in_array($keyword, $this->keywords)) {
            $this->keywords[] = $keyword;
        }
    }

    public function removeKeyword(string $keyword): void
    {
        $this->keywords = array_filter($this->keywords, fn($k) => $k !== $keyword);
    }

    public function setKeywords(array $keywords): void
    {
        $this->keywords = $keywords;
    }

    public function getRobots(): ?string
    {
        return $this->robots;
    }

    public function setRobots(string $robots): void
    {
        $this->robots = $robots;
    }

    public function getCharset(): ?string
    {
        return $this->charset;
    }

    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }
}
