<?php

/**
 * SVGFont - Une classe pour la gestion des polices SVG et la conversion des textes en paths
 * @author Lukasz Led�chowski lukasz@ledochowski.pl
 * @version 0.2
 * Version �tendue avec des fonctionnalit�s suppl�mentaires pour la conversion des textes SVG
 */
class SVGFont {

    protected $id = '';
    protected $horizAdvX = 0;
    protected $unitsPerEm = 0;
    protected $ascent = 0;
    protected $descent = 0;
    protected $glyphs = array();
    
    // Map des polices disponibles
    protected static $fontMap = [
        'Amatic' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/Amatic_700.svg',
        'Arial' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/Arial_400.svg',
        'Baroque' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/BaroqueScript.svg',
        'Baskerville' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/Libre_Baskerville_normal_400.svg',
        'Bauhaus' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/Bauhaus_500.svg',
        'Birds of Paradise' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/BirdsOfParadise.svg',
        'Comic Sans' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/ComicSans.svg',
        'Cooper' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/Cooper_Black_normal_400.svg',
        'Dancing' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/Dancing.svg',
        'Duepuntozero' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/Duepuntozero_normal_400.svg',
        'Edwardian' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/Edwardian_Script_ITC_normal_400.svg',
        'FreestyleScript' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/Freestyle_Script_normal_400.svg',
        'Harrington' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/Harrington_normal_400.svg',
        'KentuckyFriedChicken' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/KentuckyFriedChicken.svg',
        'Lucida Handwriting' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/Lucida_Handwriting_italic_400.svg',
        'Not just Groovy' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/Not_Just_Groovy_normal_400.svg',
        'Old English' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/Old_English_Text_MT_normal_400.svg',
        'Script MT Bold' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/Script.svg',
        'Phitradesign Ink' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/phitradesign_ink.svg',
        'Viksi Script' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/viksi_script.svg',
        'Mrs Saint Delafield' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/MrsSaintDelafield-Regular.svg',
        'Monotype Corsiva' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/Monotype_Corsiva_italic_400.svg',
        'Pristina' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/Pristina_normal_400.svg',
        'Ubuntu' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/Ubuntu_400.svg',
        'gentilis' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/gentilis_regular_400.svg',
        'melinda' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/melinda.svg',
        'verdana' => '/home/mon-porte-clef.fr/public_html/lib/internal/SVGFont/verdana.svg'
    ];
    
    // Synonymes de polices pour la d�tection automatique
    protected static $fontSynonyms = [
        'times' => 'Baskerville',
        'times new roman' => 'Baskerville',
        'helvetica' => 'Arial',
        'sans-serif' => 'Arial',
        'sans serif' => 'Arial',
        'serif' => 'Baskerville',
        'cursive' => 'Script MT Bold',
        'fantasy' => 'Old English',
        'monospace' => 'Ubuntu',
        'comic' => 'Comic Sans',
        'script' => 'Script MT Bold',
        'handwriting' => 'Lucida Handwriting',
        'corsiva' => 'Monotype Corsiva',
        'amatique' => 'Amatic'
    ];
    
    // Cache pour les objets de police charg�s
    protected static $loadedFonts = [];

/**
 * Fonction utf8ToUnicode corrig�e pour SVGFont
 * Corrige les erreurs de passage de valeur null � strlen() et les acc�s tableau ind�finis
 */
function utf8ToUnicode(?string $str): array
{
    // Si la cha�ne est null ou vide, retourner un espace
    if ($str === null || $str === '') {
        return [32]; // Code Unicode pour l'espace
    }

    $unicode = [];
    $values = [];
    $lookingFor = 1;

    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) {
        $thisValue = ord($str[$i]);
        
        if ($thisValue < 128) {
            $unicode[] = $thisValue;
        } else {
            if (count($values) == 0) {
                $lookingFor = ($thisValue < 224) ? 2 : 3;
            }
            
            $values[] = $thisValue;
            
            if (count($values) == $lookingFor) {
                $number = 0;
                
                if ($lookingFor == 3) {
                    $number = (($values[0] % 16) * 4096) + (($values[1] % 64) * 64) + ($values[2] % 64);
                } else {
                    $number = (($values[0] % 32) * 64) + ($values[1] % 64);
                }
                
                $unicode[] = $number;
                $values = [];
                $lookingFor = 1;
            }
        }
    }

    // S'assurer que nous avons au moins un caract�re
    if (empty($unicode)) {
        $unicode[] = 32; // Espace
    }
    
    return $unicode;
}

    /**
     * Function takes path to SVG font (local path) and processes its xml
     * to get path representation of every character and additional
     * font parameters
     */
    public function load($filename) {
        $this->glyphs = array();
        $z = new XMLReader;
        $z->open($filename);

        // move to the first <product /> node
        while ($z->read()) {
            $name = $z->name;

            if ($z->nodeType == XMLReader::ELEMENT) {
                if ($name == 'font') {
                    $this->id = $z->getAttribute('id');
                    $this->horizAdvX = $z->getAttribute('horiz-adv-x');
                }

                if ($name == 'font-face') {
                    $this->unitsPerEm = $z->getAttribute('units-per-em');
                    $this->ascent = $z->getAttribute('ascent');
                    $this->descent = $z->getAttribute('descent');
                }

                if ($name == 'glyph') {
                    $unicode = $z->getAttribute('unicode');
                    $unicode = $this->utf8ToUnicode($unicode);
                    
                    // V�rifier si le tableau est vide ou si l'index 0 n'existe pas
                    if (!empty($unicode) && isset($unicode[0])) {
                        $unicodeValue = $unicode[0];
                        
                        $this->glyphs[$unicodeValue] = new stdClass();
                        $this->glyphs[$unicodeValue]->horizAdvX = $z->getAttribute('horiz-adv-x');
                        if (empty($this->glyphs[$unicodeValue]->horizAdvX)) {
                            $this->glyphs[$unicodeValue]->horizAdvX = $this->horizAdvX;
                        }
                        $this->glyphs[$unicodeValue]->d = $z->getAttribute('d');
                    }
                }
            }
        }
    }

    /**
     * Function takes UTF-8 encoded string and size, returns xml for SVG paths representing this string.
     * @param string $text UTF-8 encoded text
     * @param int $asize size of requested text
     * @return string xml for text converted into SVG paths
     */
    function textToPaths($text, $asize) {
        // V�rifier si le texte est vide
        if (empty($text)) {
            return "<g></g>"; // Retourner un groupe vide
        }
        
        $lines = explode("\n", $text);
        $result = "";
        $horizAdvY = 0;
        
        foreach($lines as $lineText) {
            if (empty($lineText)) {
                $horizAdvY += $this->ascent + $this->descent;
                continue;
            }
            
            $unicodeChars = $this->utf8ToUnicode($lineText);
            $size = ((float)$asize) / $this->unitsPerEm;
            $result .= "<g transform=\"scale({$size}) translate(0, {$horizAdvY})\">";
            $horizAdvX = 0;
            
            for($i = 0; $i < count($unicodeChars); $i++) {
                $letter = $unicodeChars[$i];
                if (isset($this->glyphs[$letter]) && !empty($this->glyphs[$letter]->d)) {
                    $result .= "<path transform=\"translate({$horizAdvX},{$horizAdvY}) rotate(180) scale(-1, 1)\" d=\"{$this->glyphs[$letter]->d}\" />";
                    $horizAdvX += $this->glyphs[$letter]->horizAdvX;
                } else {
                    // Caract�re non trouv� ou sans chemin, utiliser un espace
                    $horizAdvX += $this->horizAdvX * 0.5; // Avancer d'un demi-espace
                }
            }
            
            $result .= "</g>";
            $horizAdvY += $this->ascent + $this->descent;
        }

        return $result;
    }
    
    /**
     * D�finir une map de polices personnalis�e
     * @param array $fontMap
     */
    public static function setFontMap($fontMap) {
        self::$fontMap = $fontMap;
    }
    
    /**
     * Ajouter des synonymes de police pour la d�tection automatique
     * @param array $synonyms
     */
    public static function addFontSynonyms($synonyms) {
        self::$fontSynonyms = array_merge(self::$fontSynonyms, $synonyms);
    }
    
    /**
     * D�tecter automatiquement la police � partir du style ou de l'attribut font-family
     * @param string $fontFamily
     * @return string Nom de la police d�tect�e
     */
    public static function detectFont($fontFamily) {
        if (empty($fontFamily)) {
            return array_key_first(self::$fontMap); // Police par d�faut
        }
        
        // Nettoyer la cha�ne de police
        $fontFamily = strtolower(trim($fontFamily));
        $fontFamily = preg_replace('/[\'"]/', '', $fontFamily); // Supprimer les guillemets
        
        // V�rifier les correspondances directes
        foreach (self::$fontMap as $font => $path) {
            if (stripos($fontFamily, strtolower($font)) !== false) {
                return $font;
            }
        }
        
        // V�rifier les synonymes
        foreach (self::$fontSynonyms as $synonym => $font) {
            if (stripos($fontFamily, $synonym) !== false) {
                return $font;
            }
        }
        
        // Police par d�faut si aucune correspondance
        return 'Arial';
    }
    
    /**
     * Extraire la taille de police du style
     * @param string $style
     * @param int $defaultSize
     * @return float
     */
    public static function extractFontSize($style, $defaultSize = 12) {
        if (preg_match('/font-size:\s*(\d+(\.\d+)?)p[xt]/', $style, $matches)) {
            return floatval($matches[1]);
        }
        return $defaultSize;
    }
    
    /**
     * Extraire la couleur du style
     * @param string $style
     * @param string $fillAttr
     * @return string
     */
    public static function extractFill($style, $fillAttr = null) {
        if (!empty($fillAttr) && $fillAttr !== 'none') {
            return $fillAttr;
        }
        
        if (preg_match('/fill:\s*([^;]+)/', $style, $matches)) {
            return trim($matches[1]);
        }
        
        return '#000000'; // Noir par d�faut
    }
    
    /**
     * Extraire la famille de police du style
     * @param string $style
     * @return string
     */
    public static function extractFontFamily($style) {
        if (preg_match('/font-family:\s*([^;]+)/', $style, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }
    
    /**
     * D�tecter si la police est en gras � partir du style ou de l'attribut
     * @param string $style
     * @param string $fontWeightAttr
     * @return bool
     */
    public static function isFontBold($style, $fontWeightAttr = null) {
        $fontWeight = $fontWeightAttr;
        
        if (empty($fontWeight) && preg_match('/font-weight:\s*([^;]+)/', $style, $matches)) {
            $fontWeight = trim($matches[1]);
        }
        
        // Consid�rer comme gras si le poids est >= 600 ou 'bold', 'bolder'
        return ($fontWeight && (
            $fontWeight >= 600 || 
            $fontWeight === 'bold' || 
            $fontWeight === 'bolder'
        ));
    }
    
    /**
     * D�tecter si la police est en italique � partir du style ou de l'attribut
     * @param string $style
     * @param string $fontStyleAttr
     * @return bool
     */
    public static function isFontItalic($style, $fontStyleAttr = null) {
        $fontStyle = $fontStyleAttr;
        
        if (empty($fontStyle) && preg_match('/font-style:\s*([^;]+)/', $style, $matches)) {
            $fontStyle = trim($matches[1]);
        }
        
        return ($fontStyle === 'italic' || $fontStyle === 'oblique');
    }
    
    /**
     * Choisir la meilleure police en fonction des attributs de style
     * @param string $fontName
     * @param bool $isBold
     * @param bool $isItalic
     * @return string
     */
    public static function chooseBestFont($fontName, $isBold, $isItalic) {
        // Police standard par d�faut
        $bestMatch = $fontName;
        
        // V�rifier si nous avons des variantes de la police
        $variants = [];
        foreach (array_keys(self::$fontMap) as $font) {
            if (stripos($font, $fontName) !== false) {
                $variants[] = $font;
            }
        }
        
        // Si nous avons des variantes, essayons de trouver la meilleure correspondance
        if (count($variants) > 1) {
            foreach ($variants as $variant) {
                $variantLower = strtolower($variant);
                
                // Chercher la correspondance exacte pour gras et italique
                if ($isBold && $isItalic && 
                    (strpos($variantLower, 'bold') !== false || strpos($variantLower, '700') !== false || strpos($variantLower, '800') !== false) &&
                    (strpos($variantLower, 'italic') !== false || strpos($variantLower, 'oblique') !== false)) {
                    return $variant;
                }
                
                // Correspondance pour gras uniquement
                if ($isBold && !$isItalic && 
                    (strpos($variantLower, 'bold') !== false || strpos($variantLower, '700') !== false || strpos($variantLower, '800') !== false) &&
                    (strpos($variantLower, 'italic') === false && strpos($variantLower, 'oblique') === false)) {
                    return $variant;
                }
                
                // Correspondance pour italique uniquement
                if (!$isBold && $isItalic && 
                    (strpos($variantLower, 'italic') !== false || strpos($variantLower, 'oblique') !== false) &&
                    (strpos($variantLower, 'bold') === false && strpos($variantLower, '700') === false && strpos($variantLower, '800') === false)) {
                    return $variant;
                }
                
                // Police normale (ni gras ni italique)
                if (!$isBold && !$isItalic && 
                    strpos($variantLower, 'regular') !== false || strpos($variantLower, 'normal') !== false || strpos($variantLower, '400') !== false) {
                    return $variant;
                }
            }
        }
        
        return $bestMatch;
    }
    
    /**
     * Charger une police SVG (avec mise en cache)
     * @param string $fontName
     * @return SVGFont
     */
    public static function loadSVGFont($fontName) {
        if (!isset(self::$fontMap[$fontName])) {
            $fontName = 'Arial'; // Fallback par d�faut
        }
        
        if (!isset(self::$loadedFonts[$fontName])) {
            $svgFont = new self();
            $svgFont->load(self::$fontMap[$fontName]);
            self::$loadedFonts[$fontName] = $svgFont;
        }
        
        return self::$loadedFonts[$fontName];
    }
    
    /**
     * Extraire tous les styles CSS importants d'un �l�ment texte
     * @param DOMElement $element
     * @param array $inheritedStyles
     * @return array
     */
    public static function extractStyles($element, $inheritedStyles = []) {
        $styles = $inheritedStyles;
        
        // Extraire les styles de l'attribut style
        if ($element->hasAttribute('style')) {
            $styleAttr = $element->getAttribute('style');
            preg_match_all('/([^:;]+):([^;]+);?/', $styleAttr, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $styles[trim($match[1])] = trim($match[2]);
            }
        }
        
        // Extraire les attributs de style individuels
        $styleAttributes = [
            'fill', 'stroke', 'stroke-width', 'font-weight', 'font-style', 
            'text-decoration', 'letter-spacing', 'word-spacing', 'opacity',
            'fill-opacity', 'stroke-opacity', 'stroke-dasharray', 'stroke-linecap',
            'stroke-linejoin', 'font-variant'
        ];
        
        foreach ($styleAttributes as $attr) {
            if ($element->hasAttribute($attr)) {
                $styles[$attr] = $element->getAttribute($attr);
            }
        }
        
        return $styles;
    }
    
    /**
     * Applique les styles extraits � un �l�ment SVG
     * @param DOMElement $element
     * @param array $styles
     * @return DOMElement
     */
    public static function applyStyles($element, $styles) {
        // Propri�t�s qui doivent �tre appliqu�es directement comme attributs
        $directAttributes = [
            'fill', 'stroke', 'stroke-width', 'opacity', 'fill-opacity', 
            'stroke-opacity', 'stroke-dasharray', 'stroke-linecap', 'stroke-linejoin'
        ];
        
        foreach ($styles as $property => $value) {
            if (in_array($property, $directAttributes)) {
                $element->setAttribute($property, $value);
            }
        }
        
        // Pour les autres propri�t�s, les combiner dans un attribut style
        $styleStr = '';
        foreach ($styles as $property => $value) {
            if (!in_array($property, $directAttributes)) {
                $styleStr .= "$property:$value;";
            }
        }
        
        if (!empty($styleStr)) {
            $element->setAttribute('style', $styleStr);
        }
        
        return $element;
    }
    
    /**
     * Fonction pour g�rer les �l�ments <tspan> et en extraire le contenu
     * @param DOMElement $textElement
     * @param DOMDocument $dom
     * @return array
     */
    public static function processTspanElements($textElement, $dom) {
        $textContent = '';
        $tspanElements = [];
        
        // V�rifier si l'�l�ment a des n�uds enfants <tspan>
        $hasTspans = false;
        foreach ($textElement->childNodes as $child) {
            if ($child->nodeName === 'tspan') {
                $hasTspans = true;
                break;
            }
        }
        
        // Si pas de tspans, retourner simplement le contenu du texte
        if (!$hasTspans) {
            return [
                'text' => $textElement->textContent,
                'tspans' => [],
                'styles' => self::extractStyles($textElement)
            ];
        }
        
        // Extraire tous les styles du texte parent pour h�ritage
        $parentStyles = self::extractStyles($textElement);
        
        // Collecter tous les �l�ments tspan avec leurs attributs
        foreach ($textElement->childNodes as $child) {
            if ($child->nodeName === 'tspan') {
                $x = $child->hasAttribute('x') ? $child->getAttribute('x') : null;
                $y = $child->hasAttribute('y') ? $child->getAttribute('y') : null;
                $dx = $child->hasAttribute('dx') ? $child->getAttribute('dx') : null;
                $dy = $child->hasAttribute('dy') ? $child->getAttribute('dy') : null;
                
                // Fusionner les styles du parent avec ceux du tspan (les styles du tspan ont la priorit�)
                $tspanStyles = self::extractStyles($child, $parentStyles);
                
                $tspanElements[] = [
                    'content' => $child->textContent,
                    'x' => $x, 
                    'y' => $y,
                    'dx' => $dx,
                    'dy' => $dy,
                    'styles' => $tspanStyles
                ];
                
                // Ajouter au contenu global avec un marqueur de retour � la ligne si n�cessaire
                $textContent .= $child->textContent . "\n";
            } elseif ($child->nodeType === XML_TEXT_NODE) {
                $textContent .= $child->textContent;
            }
        }
        
        return [
            'text' => rtrim($textContent, "\n"),
            'tspans' => $tspanElements,
            'styles' => $parentStyles
        ];
    }
    
    /**
     * Fonction pour v�rifier l'encodage et nettoyer le contenu XML probl�matique
     * @param string $content
     * @return string
     */
    public static function cleanXmlContent($content) {
        // Supprimer la d�claration XML existante s'il y en a une
        $content = preg_replace('/<\?xml[^>]+\?>/', '', $content);
        
        // Corriger les entit�s HTML communes qui peuvent causer des probl�mes
        $content = str_replace('&nbsp;', '&#160;', $content);
        
        // Corriger d'autres entit�s non standard
        //$content = preg_replace('/&(?!amp;|lt;|gt;|quot;|apos;|#\d+;|#x[0-9a-fA-F]+;)/', '&amp;', $content);
        
        return $content;
    }
    
    /**
     * Fonction principale pour convertir tous les textes d'un fichier SVG en paths
     * @param string $svgContent Contenu du fichier SVG
     * @param string $defaultFontName Police par d�faut (si non d�tect�e)
     * @param float $defaultFontSize Taille de police par d�faut (si non d�tect�e)
     * @return string SVG converti avec les textes transform�s en paths
     * @throws Exception
     */
    public static function convertSVGTextToPaths($svgContent, $defaultFontName = null, $defaultFontSize = 12) {
        // Nettoyer et pr�parer le contenu XML
        $svgContent = self::cleanXmlContent($svgContent);
        
        $dom = new DOMDocument();
        
        // Supprimer les erreurs libxml pendant le chargement
        $internalErrors = libxml_use_internal_errors(true);
        
        try {
            $dom->loadXML($svgContent);
            $errors = libxml_get_errors();
            libxml_clear_errors();
            
            // V�rifier s'il y a eu des erreurs lors du chargement
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = "Ligne {$error->line}: {$error->message}";
                }
                error_log("Erreurs XML dans le SVG: " . implode("; ", $errorMessages));
                // Continuons malgr� les erreurs, certaines peuvent �tre ignor�es
            }
            
            // Trouver tous les �l�ments de texte
            $textElements = $dom->getElementsByTagName('text');
            
            // Cr�er une liste des �l�ments � remplacer
            $elementsToReplace = [];
            
            // Cloner la liste des n�uds car nous allons modifier le DOM
            $textNodes = [];
            foreach ($textElements as $textElement) {
                $textNodes[] = $textElement;
            }
            
            foreach ($textNodes as $textElement) {
                // Obtenir les attributs du texte
                $x = $textElement->hasAttribute('x') ? $textElement->getAttribute('x') : 0;
                $y = $textElement->hasAttribute('y') ? $textElement->getAttribute('y') : 0;
                $transform = $textElement->hasAttribute('transform') ? $textElement->getAttribute('transform') : '';
                
                // Extraire tous les styles
                $styles = self::extractStyles($textElement);
                $style = $textElement->hasAttribute('style') ? $textElement->getAttribute('style') : '';
                
                // V�rifier s'il y a un textPath pour le texte qui suit un chemin
                $hasTextPath = false;
                foreach ($textElement->childNodes as $child) {
                    if ($child->nodeName === 'textPath') {
                        $hasTextPath = true;
                        break;
                    }
                }
                
                // Pour l'instant, ne pas traiter les textPath
                if ($hasTextPath) {
                    continue;
                }
                
                // D�tecter la famille de police
                $fontFamilyAttr = $textElement->hasAttribute('font-family') ? $textElement->getAttribute('font-family') : '';
                $fontFamily = !empty($fontFamilyAttr) ? $fontFamilyAttr : self::extractFontFamily($style);
                $fontName = self::detectFont($fontFamily);
                
                if ($defaultFontName && empty($fontName)) {
                    $fontName = $defaultFontName;
                }
                
                // D�tecter la taille de police
                $fontSize = $textElement->hasAttribute('font-size') ? 
                            $textElement->getAttribute('font-size') : 
                            self::extractFontSize($style, $defaultFontSize);
                
                // Supprimer 'px' ou 'pt' si pr�sent
                $fontSize = preg_replace('/px|pt/', '', $fontSize);
                
                // D�tecter le style de la police (gras, italique)
                $isBold = self::isFontBold($style, $textElement->hasAttribute('font-weight') ? $textElement->getAttribute('font-weight') : null);
                $isItalic = self::isFontItalic($style, $textElement->hasAttribute('font-style') ? $textElement->getAttribute('font-style') : null);
                
                // Choisir la meilleure variante de police disponible
                $bestFont = self::chooseBestFont($fontName, $isBold, $isItalic);
                
                // Traiter le contenu du texte en tenant compte des tspans
                $processedText = self::processTspanElements($textElement, $dom);
                $text = $processedText['text'];
                $tspans = $processedText['tspans'];
                
                // Charger la police SVG
                $svgFont = self::loadSVGFont($bestFont);
                
                // Cr�er un groupe principal pour contenir les paths
                $group = $dom->createElementNS('http://www.w3.org/2000/svg', 'g');
                
                if ($transform) {
                    $group->setAttribute('transform', $transform);
                }
                
                // Appliquer les styles de base au groupe principal
                $group = self::applyStyles($group, $styles);
                
                // Si nous avons des tspans, traiter chaque ligne s�par�ment
                if (!empty($tspans)) {
                    $currentY = 0;
                    
                    foreach ($tspans as $index => $tspan) {
                        // Calculer la position y relative
                        if ($tspan['y'] !== null) {
                            $lineY = floatval($tspan['y']);
                        } else if ($tspan['dy'] !== null) {
                            $currentY += floatval($tspan['dy']);
                            $lineY = $currentY;
                        } else if ($index > 0) {
                            // Si pas de y ou dy sp�cifi�, augmenter de la taille de la police
                            $currentY += floatval($fontSize) * 1.2; // Interligne approximatif
                            $lineY = $currentY;
                        } else {
                            $lineY = 0;
                        }
                        
                        // Calculer la position x relative
                        $lineX = ($tspan['x'] !== null) ? floatval($tspan['x']) : 
                                 (($tspan['dx'] !== null) ? floatval($tspan['dx']) : 0);
                        
                        // Cr�er les paths pour cette ligne
                        $paths = $svgFont->textToPaths($tspan['content'], $fontSize);
                        
                        // Cr�er un sous-groupe pour cette ligne
                        $lineGroup = $dom->createElementNS('http://www.w3.org/2000/svg', 'g');
                        $lineTransform = "translate($lineX, $lineY)";
                        $lineGroup->setAttribute('transform', $lineTransform);
                        
                        // Appliquer les styles sp�cifiques � ce tspan
                        $lineGroup = self::applyStyles($lineGroup, $tspan['styles']);
                        
                        // Ajouter les paths au sous-groupe
                        $tempDom = new DOMDocument();
                        try {
                            $tempDom->loadXML('<svg xmlns="http://www.w3.org/2000/svg">' . $paths . '</svg>');
                            $pathNodes = $tempDom->documentElement->childNodes;
                            
                            foreach ($pathNodes as $pathNode) {
                                $importedNode = $dom->importNode($pathNode, true);
                                $lineGroup->appendChild($importedNode);
                            }
                            
                            // Ajouter le sous-groupe au groupe principal
                            $group->appendChild($lineGroup);
                        } catch (Exception $e) {
                            error_log("Erreur lors du chargement des paths pour tspan: " . $e->getMessage());
                            // On continue avec les autres tspans
                        }
                    }
                } else {
                    // Pas de tspans, traiter le texte en une seule fois
                    $paths = $svgFont->textToPaths($text, $fontSize);
                    
                    try {
                        // Ajouter les paths au DOM
                        $tempDom = new DOMDocument();
                        $tempDom->loadXML('<svg xmlns="http://www.w3.org/2000/svg">' . $paths . '</svg>');
                        $pathNodes = $tempDom->documentElement->childNodes;
                        
                        foreach ($pathNodes as $pathNode) {
                            $importedNode = $dom->importNode($pathNode, true);
                            $group->appendChild($importedNode);
                        }
                    } catch (Exception $e) {
                        error_log("Erreur lors du chargement des paths pour texte: " . $e->getMessage());
                        // On continue avec les autres �l�ments de texte
                    }
                }
                
                // Si le texte principal n'a pas de tspans, positionner le groupe
                if (empty($tspans)) {
                    $currentTransform = $group->getAttribute('transform');
                    $newTransform = "translate($x, $y) " . $currentTransform;
                    $group->setAttribute('transform', $newTransform);
                }
                
                // Ajouter l'�l�ment � la liste de remplacement
                $elementsToReplace[] = [
                    'original' => $textElement,
                    'replacement' => $group
                ];
            }
            
            // Remplacer les �l�ments texte par les paths
            foreach ($elementsToReplace as $replacement) {
                $parent = $replacement['original']->parentNode;
                if ($parent) {
                    $parent->replaceChild($replacement['replacement'], $replacement['original']);
                }
            }
            
            return $dom->saveXML();
        } catch (Exception $e) {
            error_log("Erreur lors de la conversion du SVG: " . $e->getMessage());
            throw new Exception("Erreur lors de la conversion du SVG: " . $e->getMessage());
        } finally {
            // Restaurer le comportement d'erreur libxml
            libxml_use_internal_errors($internalErrors);
        }
    }
    
}