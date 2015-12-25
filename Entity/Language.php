<?php
/**
 * @author		Can Berkol
 * @author		Said İmamoğlu
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com) (C) 2015
 * @license     GPLv3
 *
 * @date        23.12.2015
 */
namespace BiberLtd\Bundle\MultiLanguageSupportBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;
use BiberLtd\Bundle\CoreBundle\CoreEntity;
/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="language",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={@ORM\Index(name="idxNLanguageSchema", columns={"`schema`"})},
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="idxULanguageId", columns={"id"}),
 *         @ORM\UniqueConstraint(name="idxULanguageUrlKey", columns={"url_key","site"}),
 *         @ORM\UniqueConstraint(name="idxULanguageIsoCode", columns={"iso_code","site"})
 *     }
 * )
 */
class Language extends CoreEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", length=5)
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=155, nullable=false, name="`name`")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @var string
     */
    private $url_key;

    /**
     * @ORM\Column(type="string", length=7, nullable=true)
     * @var string
     */
    private $iso_code;

    /**
     * @ORM\Column(type="string", length=3, nullable=false, name="`schema`", options={"default":"ltr"})
     * @var string
     */
    private $schema;

    /** 
     * @ORM\Column(type="string", length=1, nullable=false, name="`status`", options={"default":"a"})
     * @var string
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\SiteManagementBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", onDelete="CASCADE")
     * @var \BiberLtd\Bundle\SiteManagementBundle\Entity\Site
     */
    private $site;

	/**
	 * @return mixed
	 */
    public function getId(){
        return $this->id;
    }

	/**
	 * @param string $iso_code
	 *
	 * @return $this
	 */
    public function setIsoCode(\string $iso_code) {
        if(!$this->setModified('iso_code', $iso_code)->isModified()) {
            return $this;
        }
		$this->iso_code = $iso_code;
		return $this;
    }

	/**
	 * @return string
	 */
    public function getIsoCode() {
        return $this->iso_code;
    }

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
    public function setName(\string $name) {
        if(!$this->setModified('name', $name)->isModified()) {
            return $this;
        }
		$this->name = $name;
		return $this;
    }

	/**
	 * @return string
	 */
    public function getName() {
        return $this->name;
    }

	/**
	 * @param string $schema
	 *
	 * @return $this
	 */
    public function setSchema(\string $schema) {
        if(!$this->setModified('schema', $schema)->isModified()) {
            return $this;
        }
		$this->schema = $schema;
		return $this;
    }

	/**
	 * @return string
	 */
    public function getSchema() {
        return $this->schema;
    }

	/**
	 * @param \BiberLtd\Bundle\SiteManagementBundle\Entity\Site $site
	 *
	 * @return $this
	 */
    public function setSite(\BiberLtd\Bundle\SiteManagementBundle\Entity\Site $site) {
        if(!$this->setModified('site', $site)->isModified()) {
            return $this;
        }
		$this->site = $site;
		return $this;
    }

	/**
	 * @return \BiberLtd\Bundle\SiteManagementBundle\Entity\Site
	 */
    public function getSite() {
        return $this->site;
    }

	/**
	 * @param string $url_key
	 *
	 * @return $this
	 */
    public function setUrlKey(\string $url_key) {
        if(!$this->setModified('url_key', $url_key)->isModified()) {
            return $this;
        }
		$this->url_key = $url_key;
		return $this;
    }

	/**
	 * @return string
	 */
    public function getUrlKey() {
        return $this->url_key;
    }

	/**
	 * @param string $status
	 *
	 * @return $this
	 */
    public function setStatus(\string $status) {
        if($this->setModified('status', $status)->isModified()) {
            $this->status = $status;
        }

        return $this;
    }

	/**
	 * @return string
	 */
    public function getStatus() {
        return $this->status;
    }

}