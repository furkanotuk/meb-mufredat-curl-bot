<?php
stream_context_set_default(
    array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
        ),
    )
);

$url = 'https://mufredat.meb.gov.tr/Programlar.aspx';

$content = file_get_contents($url);

if ($content !== false) {
    $dom = new DOMDocument();

    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
    libxml_use_internal_errors(false);

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
                    $icerikListesi[] = trim($link->textContent);
                }
                $kategori['icerik'] = $icerikListesi;
            }

            $konuBasliklari[] = $kategori;
        }
    }

    $jsonData = json_encode($konuBasliklari, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    header('Content-Type: application/json; charset=utf-8');

    echo $jsonData;
} else {
    echo "Veri çekme işlemi başarısız oldu.";
}
?>
