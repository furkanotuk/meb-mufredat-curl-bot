<?php
stream_context_set_default(
        array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
            ),
        )
    );
function veriCek($url) {
    $content = file_get_contents($url);

    if ($content !== false) {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
        libxml_use_internal_errors(false);

        $aElementleri = $dom->getElementsByTagName('a');
        foreach ($aElementleri as $aElementi) {
            $hrefDegeri = $aElementi->getAttribute('href');
            if (strpos($hrefDegeri, 'Dosyalar/') === 0) {
                $linkyazdir = 'http://mufredat.meb.gov.tr/'.$hrefDegeri;
                header("Location: ".$linkyazdir);
            }
        }
        return "Dosyalar/ ile başlayan href bulunamadı.";
    } else {
        return "Veri çekme işlemi başarısız oldu.";
    }
}


if(isset($_GET['pdf'])){
    echo veriCek($_GET['pdf']);
}else{
    
    // Veriyi çekmek için URL'yi belirtin
    $url = 'https://mufredat.meb.gov.tr/Programlar.aspx';
    
    // Veriyi çekin
    $content = file_get_contents($url);
    
    if ($content !== false) {
        // DOMDocument oluşturun
        $dom = new DOMDocument();
    
        // HTML içeriğini yükleyin
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
        libxml_use_internal_errors(false);
    
        // Konu başlıklarını ve içerikleri çekin
        $konuBasliklari = array();
        $accordion = $dom->getElementById('accordion');
        if ($accordion) {
            $basliklar = $accordion->getElementsByTagName('h4');
            foreach ($basliklar as $baslik) {
                $kategori = array();
                $kategori['kategori'] = trim($baslik->textContent);
    
                $collapseId = $baslik->getAttribute('data-target');
                $trimmedId = ltrim($collapseId, '#');
                $collapse = $dom->getElementById(trim($trimmedId));
                if ($collapse) {
                    $icerikler = $collapse->getElementsByTagName('li');
                    $icerikListesi = array();
                    foreach ($icerikler as $icerik) {
                        $link = $icerik->getElementsByTagName('a')->item(0);
                        $href = $link->getAttribute('href');
                        $dosya_path = 'https://mufredat.meb.gov.tr/'.$href;
                        $icerikListesi[] = array(
                            'konu' => trim($link->textContent),
                            'link' => 'https://furkanotuk.com/api/mufredat.php?pdf='.trim($dosya_path),
                        );
    
                    }
                    $kategori['icerik'] = $icerikListesi;
                }
    
                // Kategoriyi diziye ekle
                $konuBasliklari[] = $kategori;
            }
        }
    
        // JSON formatına dönüştür
        $jsonData = json_encode($konuBasliklari, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
        // Türkçe karakterleri düzgün görüntülemek için utf-8 kodlamasını ayarlayın
        header('Content-Type: application/json; charset=utf-8');
    
        // JSON'ı ekrana yazdır
        echo $jsonData;
    } else {
        echo "Veri çekme işlemi başarısız oldu.";
    }
}
?>
