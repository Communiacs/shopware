<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Models\Article;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Article\Configurator\Option;
use Shopware\Models\Attribute\Article as ProductAttribute;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_articles_details")
 */
class Detail extends ModelEntity
{
    /**
     * OWNING SIDE
     *
     * @var Article
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Article", inversedBy="details")
     * @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $article;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<\Shopware\Models\Article\Price>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Price", mappedBy="detail", orphanRemoval=true, cascade={"persist"})
     */
    protected $prices;

    /**
     * INVERSE SIDE
     *
     * @var ProductAttribute
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Article", mappedBy="articleDetail", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * OWNING SIDE
     *
     * @var Unit
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Unit", inversedBy="articles", cascade={"persist"})
     * @ORM\JoinColumn(name="unitID", referencedColumnName="id")
     */
    protected $unit;

    /**
     * OWNING SIDE
     *
     * @var ArrayCollection<\Shopware\Models\Article\Configurator\Option>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Configurator\Option", inversedBy="articles")
     * @ORM\JoinTable(name="s_article_configurator_option_relations",
     *     joinColumns={
     *         @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="option_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $configuratorOptions;

    /**
     * INVERSE SIDE
     *
     * @var Esd
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Esd", mappedBy="articleDetail", orphanRemoval=true, cascade={"persist"})
     */
    protected $esd;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<\Shopware\Models\Article\Notification>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Notification", mappedBy="articleDetail")
     */
    protected $notifications;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<\Shopware\Models\Article\Image>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Image", mappedBy="articleDetail", orphanRemoval=true, cascade={"persist"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $images;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="articleID", type="integer", nullable=false)
     */
    private $articleId;

    /**
     * @var int
     *
     * @ORM\Column(name="unitID", type="integer", nullable=true)
     */
    private $unitId;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Regex("/^[a-zA-Z0-9-_.]+$/")
     *
     * @ORM\Column(name="ordernumber", type="string", nullable=false, unique=true)
     */
    private $number = '';

    /**
     * @var string
     *
     * @ORM\Column(name="suppliernumber", type="string", nullable=true)
     */
    private $supplierNumber;

    /**
     * @var int
     *
     * @ORM\Column(name="kind", type="integer", nullable=false)
     */
    private $kind = 2;

    /**
     * @var string
     *
     * @ORM\Column(name="additionaltext", type="string", nullable=true)
     */
    private $additionalText;

    /**
     * @var int
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = false;

    /**
     * @var int
     *
     * @ORM\Column(name="instock", type="integer", nullable=false)
     */
    private $inStock = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="stockmin", type="integer", nullable=true)
     */
    private $stockMin;

    /**
     * @var int
     *
     * @ORM\Column(name="laststock", type="boolean", nullable=false)
     */
    private $lastStock;

    /**
     * @var float
     *
     * @ORM\Column(name="weight", type="decimal", nullable=true, precision=3)
     */
    private $weight;

    /**
     * @var float
     *
     * @ORM\Column(name="width", type="decimal", nullable=true, precision=3)
     */
    private $width;

    /**
     * @var float
     *
     * @ORM\Column(name="length", type="decimal", nullable=true, precision=3)
     */
    private $len;

    /**
     * @var float
     *
     * @ORM\Column(name="height", type="decimal", nullable=true, precision=3)
     */
    private $height;

    /**
     * @var string
     *
     * @ORM\Column(name="ean", type="string", nullable=true)
     */
    private $ean;

    /**
     * @var float
     *
     * @ORM\Column(name="purchaseprice", type="decimal", nullable=false)
     */
    private $purchasePrice = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="minpurchase", type="integer", nullable=false)
     */
    private $minPurchase = 1;

    /**
     * @var int
     *
     * @ORM\Column(name="purchasesteps", type="integer", nullable=true)
     */
    private $purchaseSteps;

    /**
     * @var int
     *
     * @ORM\Column(name="maxpurchase", type="integer", nullable=true)
     */
    private $maxPurchase;

    /**
     * @var float
     *
     * @ORM\Column(name="purchaseunit", type="decimal", nullable=true)
     */
    private $purchaseUnit;

    /**
     * @var float
     *
     * @ORM\Column(name="referenceunit", type="decimal", nullable=true)
     */
    private $referenceUnit;

    /**
     * @var string
     *
     * @ORM\Column(name="packunit", type="text", nullable=true)
     */
    private $packUnit;

    /**
     * @var int
     *
     * @ORM\Column(name="shippingfree", type="boolean", nullable=false)
     */
    private $shippingFree = false;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="releasedate", type="date", nullable=true)
     */
    private $releaseDate;

    /**
     * @var string
     *
     * @ORM\Column(name="shippingtime", type="string", length=11, nullable=true)
     */
    private $shippingTime;

    /**
     * Class constructor. Initials the array collections.
     */
    public function __construct()
    {
        $this->prices = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->configuratorOptions = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set number
     *
     * @param string $number
     *
     * @return Detail
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string|null
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set supplierNumber
     *
     * @param string $supplierNumber
     *
     * @return Detail
     */
    public function setSupplierNumber($supplierNumber)
    {
        $this->supplierNumber = $supplierNumber;

        return $this;
    }

    /**
     * Get supplierNumber
     *
     * @return string
     */
    public function getSupplierNumber()
    {
        return $this->supplierNumber;
    }

    /**
     * Set kind
     *
     * @param int $kind
     *
     * @return Detail
     */
    public function setKind($kind)
    {
        $this->kind = $kind;

        return $this;
    }

    /**
     * Get kind
     *
     * @return int
     */
    public function getKind()
    {
        return $this->kind;
    }

    /**
     * Set additionalText
     *
     * @param string $additionalText
     *
     * @return Detail
     */
    public function setAdditionalText($additionalText)
    {
        $this->additionalText = $additionalText;

        return $this;
    }

    /**
     * Get additionalText
     *
     * @return string
     */
    public function getAdditionalText()
    {
        return $this->additionalText;
    }

    /**
     * Set active
     *
     * @param int $active
     *
     * @return Detail
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set inStock
     *
     * @param int $inStock
     *
     * @return Detail
     */
    public function setInStock($inStock)
    {
        $this->inStock = (int) $inStock;

        return $this;
    }

    /**
     * Get inStock
     *
     * @return int
     */
    public function getInStock()
    {
        return $this->inStock;
    }

    /**
     * Set stockMin
     *
     * @param int $stockMin
     *
     * @return Detail
     */
    public function setStockMin($stockMin)
    {
        $this->stockMin = $stockMin;

        return $this;
    }

    /**
     * Get stockMin
     *
     * @return int
     */
    public function getStockMin()
    {
        return $this->stockMin;
    }

    /**
     * Set lastStock
     *
     * @param int $lastStock
     *
     * @return Detail
     */
    public function setLastStock($lastStock)
    {
        $this->lastStock = $lastStock;

        return $this;
    }

    /**
     * Get lastStock
     *
     * @return int
     */
    public function getLastStock()
    {
        return $this->lastStock;
    }

    /**
     * Set weight
     *
     * @param float $weight
     *
     * @return Detail
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set position
     *
     * @param int $position
     *
     * @return Detail
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getArticleId()
    {
        return $this->articleId;
    }

    /**
     * @return Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @return Detail
     */
    public function setArticle(Article $article)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * @return ProductAttribute|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param ProductAttribute|array|null $attribute
     *
     * @return Detail
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, ProductAttribute::class, 'attribute', 'articleDetail');
    }

    /**
     * @return ArrayCollection
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param Price[]|null $prices
     *
     * @return Detail
     */
    public function setPrices($prices)
    {
        return $this->setOneToMany($prices, Price::class, 'prices', 'detail');
    }

    /**
     * @return float
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param float $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return float
     */
    public function getLen()
    {
        return $this->len;
    }

    /**
     * @param float $length
     */
    public function setLen($length)
    {
        $this->len = $length;
    }

    /**
     * @return float
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param float $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return string
     */
    public function getEan()
    {
        return $this->ean;
    }

    /**
     * @param string $ean
     */
    public function setEan($ean)
    {
        $this->ean = $ean;
    }

    /**
     * Set purchase price
     *
     * @param float $purchasePrice
     *
     * @return Detail
     */
    public function setPurchasePrice($purchasePrice)
    {
        $this->purchasePrice = $purchasePrice;

        return $this;
    }

    /**
     * Get purchase price
     *
     * @return float
     */
    public function getPurchasePrice()
    {
        return $this->purchasePrice;
    }

    /**
     * Set shipping time
     *
     * @param string $shippingTime
     *
     * @return Detail
     */
    public function setShippingTime($shippingTime)
    {
        $this->shippingTime = $shippingTime;

        return $this;
    }

    /**
     * Get shipping time
     *
     * @return string
     */
    public function getShippingTime()
    {
        return $this->shippingTime;
    }

    /**
     * Set shippingFree
     *
     * @param int $shippingFree
     *
     * @return Detail
     */
    public function setShippingFree($shippingFree)
    {
        $this->shippingFree = $shippingFree;

        return $this;
    }

    /**
     * Get shippingFree
     *
     * @return int
     */
    public function getShippingFree()
    {
        return $this->shippingFree;
    }

    /**
     * Set releaseDate
     *
     * @param \DateTimeInterface|string|null $releaseDate
     *
     * @return Detail
     */
    public function setReleaseDate($releaseDate = null)
    {
        if ($releaseDate !== null && !($releaseDate instanceof \DateTimeInterface)) {
            $this->releaseDate = new \DateTime($releaseDate);
        } else {
            $this->releaseDate = $releaseDate;
        }

        return $this;
    }

    /**
     * Get releaseDate
     *
     * @return \DateTimeInterface
     */
    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    /**
     * Set minPurchase
     *
     * @param int $minPurchase
     *
     * @return Detail
     */
    public function setMinPurchase($minPurchase)
    {
        if ($minPurchase <= 0) {
            $minPurchase = 1;
        }

        $this->minPurchase = $minPurchase;

        return $this;
    }

    /**
     * Get minPurchase
     *
     * @return int
     */
    public function getMinPurchase()
    {
        return $this->minPurchase;
    }

    /**
     * Set purchaseSteps
     *
     * @param int $purchaseSteps
     *
     * @return Detail
     */
    public function setPurchaseSteps($purchaseSteps)
    {
        $this->purchaseSteps = $purchaseSteps;

        return $this;
    }

    /**
     * Get purchaseSteps
     *
     * @return int
     */
    public function getPurchaseSteps()
    {
        return $this->purchaseSteps;
    }

    /**
     * Set maxPurchase
     *
     * @param int $maxPurchase
     *
     * @return Detail
     */
    public function setMaxPurchase($maxPurchase)
    {
        $this->maxPurchase = $maxPurchase;

        return $this;
    }

    /**
     * Get maxPurchase
     *
     * @return int
     */
    public function getMaxPurchase()
    {
        return $this->maxPurchase;
    }

    /**
     * Set purchaseUnit
     *
     * @param float $purchaseUnit
     *
     * @return Detail
     */
    public function setPurchaseUnit($purchaseUnit)
    {
        $this->purchaseUnit = $purchaseUnit;

        return $this;
    }

    /**
     * Get purchaseUnit
     *
     * @return float
     */
    public function getPurchaseUnit()
    {
        return $this->purchaseUnit;
    }

    /**
     * Set referenceUnit
     *
     * @param float $referenceUnit
     *
     * @return Detail
     */
    public function setReferenceUnit($referenceUnit)
    {
        $this->referenceUnit = $referenceUnit;

        return $this;
    }

    /**
     * Get referenceUnit
     *
     * @return float
     */
    public function getReferenceUnit()
    {
        return $this->referenceUnit;
    }

    /**
     * Set packUnit
     *
     * @param string $packUnit
     *
     * @return Detail
     */
    public function setPackUnit($packUnit)
    {
        $this->packUnit = $packUnit;

        return $this;
    }

    /**
     * Get packUnit
     *
     * @return string
     */
    public function getPackUnit()
    {
        return $this->packUnit;
    }

    /**
     * OWNING SIDE
     * of the association between articles and unit
     *
     * @return Unit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param Unit|array|null $unit
     *
     * @return Detail
     */
    public function setUnit($unit)
    {
        return $this->setManyToOne($unit, Unit::class, 'unit');
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Article\Configurator\Option>|null
     */
    public function getConfiguratorOptions()
    {
        return $this->configuratorOptions;
    }

    /**
     * @param ArrayCollection<\Shopware\Models\Article\Configurator\Option> $configuratorOptions
     */
    public function setConfiguratorOptions($configuratorOptions)
    {
        $this->configuratorOptions = $configuratorOptions;
    }

    /**
     * @param Esd|null $esd
     */
    public function setEsd($esd)
    {
        $this->esd = $esd;
    }

    /**
     * @return Esd|null
     */
    public function getEsd()
    {
        return $this->esd;
    }

    /**
     * @return ArrayCollection<Image>
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param ArrayCollection<Image>|Image[]|null $images
     *
     * @return Detail
     */
    public function setImages($images)
    {
        return $this->setOneToMany($images, Image::class, 'images', 'articleDetail');
    }
}
