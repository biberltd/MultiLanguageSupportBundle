<?php
/**
 * @name        Language
 * @package		BiberLtd\Bundle\CoreBundle\MultiLanguageSupportBundle
 *
 * @author		Can Berkol
 *              Murat Ünal
 * @version     1.0.5
 * @date        30.04.2015
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Model / Entity class.
 *
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
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=155, nullable=false, name="`name`")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $url_key;

    /**
     * @ORM\Column(type="string", length=7, nullable=true)
     */
    private $iso_code;

    /**
     * @ORM\Column(type="string", length=3, nullable=false, name="`schema`")
     */
    private $schema;

    /** 
     * @ORM\Column(type="string", length=1, nullable=false, name="`status`")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\SiteManagementBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", onDelete="CASCADE")
     */
    private $site;
    /******************************************************************
     * PUBLIC SET AND GET FUNCTIONS                                   *
     ******************************************************************/

    /**
     * @name            getId()
     *  				Gets $id property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          integer          $this->id
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @name            setIsoCode(
	 *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $iso_code
     *
     * @return          object                $this
     */
    public function setIsoCode($iso_code) {
        if(!$this->setModified('iso_code', $iso_code)->isModified()) {
            return $this;
        }
		$this->iso_code = $iso_code;
		return $this;
    }

    /**
     * @name            getIsoCode()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->iso_code
     */
    public function getIsoCode() {
        return $this->iso_code;
    }

    /**
     * @name            setName()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $name
     *
     * @return          object                $this
     */
    public function setName($name) {
        if(!$this->setModified('name', $name)->isModified()) {
            return $this;
        }
		$this->name = $name;
		return $this;
    }

    /**
     * @name            getName()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @name            setSchema ()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $schema
     *
     * @return          object                $this
     */
    public function setSchema($schema) {
        if(!$this->setModified('schema', $schema)->isModified()) {
            return $this;
        }
		$this->schema = $schema;
		return $this;
    }

    /**
     * @name            getSchema ()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->schema
     */
    public function getSchema() {
        return $this->schema;
    }

    /**
     * @name            setSite ()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $site
     *
     * @return          object                $this
     */
    public function setSite($site) {
        if(!$this->setModified('site', $site)->isModified()) {
            return $this;
        }
		$this->site = $site;
		return $this;
    }

    /**
     * @name            getSite ()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->site
     */
    public function getSite() {
        return $this->site;
    }

    /**
     * @name            setUrlKey ()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $url_key
     *
     * @return          object                $this
     */
    public function setUrlKey($url_key) {
        if(!$this->setModified('url_key', $url_key)->isModified()) {
            return $this;
        }
		$this->url_key = $url_key;
		return $this;
    }

    /**
     * @name            getUrlKey ()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->url_key
     */
    public function getUrlKey() {
        return $this->url_key;
    }

    /**
     * @name            setStatus ()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.4
     * @version         1.0.4
     *
     * @use             $this->setModified()
     *
     * @param           mixed $status
     *
     * @return          object                $this
     */
    public function setStatus($status) {
        if($this->setModified('status', $status)->isModified()) {
            $this->status = $status;
        }

        return $this;
    }

    /**
     * @name            getStatus ()
	 *
     * @author          Can Berkol
     *
     * @since           1.0.4
     * @version         1.0.4
     *
     * @return          mixed           $this->status
     */
    public function getStatus() {
        return $this->status;
    }

}
/**
 * Change Log:
 * **************************************
 * v1.0.5                      30.04.2015
 * TW #
 * Can Berkol
 * **************************************
 * ORM Changes.
 *
 * **************************************
 * v1.0.4                      Can Berkol
 * 04.03.2014
 * **************************************
 * A getStatus()
 * A setStatus()
 *
 * **************************************
 * v1.0.3                      Can Berkol
 * 29.01.2014
 * **************************************
 * M Now extends CoreEntity
 *
 * **************************************
 * v1.0.2                      Can Berkol
 * 04.08.2013
 * **************************************
 * M Non-core functionalities have been commented out.
 *
 * **************************************
 * v1.0.2                     Murat Ünal
 * 10.10.2013
 * **************************************
 * A getFilesOfPage()
 * A getId()
 * A getIsoCode()
 * A getName()
 * A getSchema()
 * A getSite()
 * A getUrlKey()
 *
 * A setFilesOfPage()
 * A setIsoCode()
 * A setName()
 * A setSchema()
 * A setSite()
 * A setUrlKey()
 *
 *
 */