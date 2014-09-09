<?php
/**
 * sys.tr.php
 *
 * Bu dosya ilgili paketin sistem (hata ve başarı) mesajlarını Türkçe olarak barındırır.
 *
 * @vendor      BiberLtd
 * @package		Core\Bundles\MultiLanguageSupportBundle
 * @subpackage	Resources
 * @name	    sys.tr.php
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.0
 * @date        03.08.2013
 *
 * =============================================================================================================
 * !!! ÖNEMLİ !!!
 *
 * Çalıştığınız sunucu ortamına göre Symfony ön belleğini temizlemek için işbu dosyayı her değiştirişinizden sonra
 * aşağıdaki komutu çalıştırmalısınız veya app/cache klasörünü silmelisiniz. Aksi takdir de tercümelerde
 * yapmış olduğunuz değişiklikler işleme alıalınmayacaktır.
 *
 * $ sudo -u apache php app/console cache:clear
 * VEYA
 * $ php app/console cache:clear
 * =============================================================================================================
 * TODOs:
 * Yok
 */
/** Nested keys are accepted */
return array(
    /** Hata mesajları */
    'err'       => array(
        /** Multi Language Support Model */
        'mlsm'   => array(
            'duplicate'      => array(
                 'language'     => 'Veri tabanında belirttiğiniz id, iso_code veya url_key değerine sahip başka bir dil bulunuyor.',
            ),
            'invalid'       =>  array(
                'entity'    => array(
                    'language'  => '"Language" objesi beklenirken farklı bir değer bulundu.',
                ),
                'parameter'     =>  array(
                    'by'        => '"$by" parametresi "entity," "id," "iso_code," veya "url_key" değerlerinden birini alabilir.',
                    'language'  => '"$language" parametresi sadece Language objesi veya tek boyutlu ve anahtar => değer eşlemeli bir Array (dizi) kabul etmektedir.',
                    'languages' => '"$languages" parametresi sadece Array (dizi) değeri kabul etmektedir.',
                    'sortorder' => '"$sortorder" parametersi sadece tek boyutlu ve anahtar => değer eşleşmeli bir dizi kabul eder. Dizide kullanılabilecek anahtarlar şunlardır: id, name, url_key, iso_code',
                ),
            ),
            'not_found'     => 'Aradığınız  dil veri tabanında bulunamadı',
            'unknown'                   => 'Bilinmeyen bir hata oluştu, lütfen doğrı olarak MultiLanguageSiteModel objesinin yaratılabildiğinden emin olun..',
        ),
    ),
    /** Başarı mesajları */
    'scc'       => array(
        /** Multi Language Support Model */
        'mlsm'   => array(
            'default'       => 'Veriler başarıyla işlendi.',
            'deleted'       => 'Veri(ler) başarıyla veri tabanından silindi.',
            'inserted'      => array(
                'multiple'      => 'Veriler başarıyla veri tabanına eklendi.',
                'single'        => 'Veri başarıyla veri tabanına eklendi.',
            ),
            'updated'       => array(
                'multiple'      => 'Veriler başarıyla güncellendi.',
                'single'        => 'Veri başarıyla güncellendi.',
            ),
        ),
    ),
);
/**
 * Change Log / Değişiklik Kaydı
 * **************************************
 * v1.0.0                      Can Berkol
 * 03.08.2013
 * **************************************
 * A err
 * A err.mlsm
 * A err.mlsm.duplicate
 * A err.mlsm.duplicate.language
 * A err.mlsm.invalid
 * A err.mlsm.invalid.entry
 * A err.mlsm.invalid.entry.language
 * A err.mlsm.invalid.parameter
 * A err.mlsm.invalid.parameter.by
 * A err.mlsm.invalid.parameter.languages
 * A err.mlsm.unknown
 * A scc
 * A scc.smm
 * A scc.smm.default
 * A scc.smm.deleted
 * A scc.smm.inserted
 * A scc.smm.inserted.multiple
 * A scc.smm.inserted.single
 * A scc.smm.updated
 * A scc.smm.updated.multiple
 * A scc.smm.updated.single
 */