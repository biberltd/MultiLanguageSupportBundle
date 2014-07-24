<?php
/**
 * @name        Language
 * @package		BiberLtd\Core\MultiLanguageSupportBundle
 *
 * @author		Can Berkol
 *              Murat Ünal
 * @version     1.0.4
 * @date        04.03.2014
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Model / Entity class.
 *
 */
namespace BiberLtd\Core\Bundles\MultiLanguageSupportBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;
use BiberLtd\Core\CoreEntity;
/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="language",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={
 *         @ORM\Index(name="idx_u_language_iso_code", columns={"iso_code","site"}),
 *         @ORM\Index(name="idx_n_language_schema", columns={"`schema`"})
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="idx_u_language_id", columns={"id"}),
 *         @ORM\UniqueConstraint(name="idx_u_language_url_key", columns={"url_key","site"})
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
     * @ORM\ManyToOne(targetEntity="BiberLtd\Core\Bundles\SiteManagementBundle\Entity\Site")
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
     * @name                  set İsoCode()
     *                            Sets the iso_code property.
     *                            Updates the data only if stored value and value to be set are different.
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
     * @name            get İsoCode()
     *                      Returns the value of iso_code property.
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
     *                  Sets the name property.
     *                  Updates the data only if stored value and value to be set are different.
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
     *                  Returns the value of name property.
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
     *                  Sets the schema property.
     *                  Updates the data only if stored value and value to be set are different.
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
     *                            Returns the value of schema property.
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
     * @name                  setSite ()
     *                                Sets the site property.
     *                                Updates the data only if stored value and value to be set are different.
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
     *                          Returns the value of site property.
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
     * @name                  setUrlKey ()
     *                                  Sets the url_key property.
     *                                  Updates the data only if stored value and value to be set are different.
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
     *                            Returns the value of url_key property.
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
     *                  Sets the status property.
     *                  Updates the data only if stored value and value to be set are different.
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
     *                  Returns the value of status property.
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