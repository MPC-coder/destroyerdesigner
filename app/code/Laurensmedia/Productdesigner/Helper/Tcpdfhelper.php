<?php
namespace Laurensmedia\Productdesigner\Helper;

class Tcpdfhelper
{
    /**
     * Original function - updated to use BP constant
     * 
     * @param string $baseDir (not used anymore, kept for backward compatibility)
     * @return \TCPDF
     */
    function getPdfObject($baseDir){
        // Utiliser un chemin absolu indépendant de $baseDir
        $magentoRoot = BP; // BP est défini par Magento et pointe vers la racine du projet
        require_once($magentoRoot . '/lib/internal/tcpdf/tcpdf.php');
        
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        return $pdf;
    }
    
   
    
    /**
     * Standardise les noms de polices dans le contenu SVG pour les adapter à la nomenclature de SVGFont
     * 
     * @param string $svgContent Contenu SVG
     * @return string SVG avec polices standardisées
     */
    function standardizeFonts($svgContent) {
        // Cette fonction prépare les noms de police pour qu'ils correspondent à ceux dans SVGFont
        // Nous inversons les remplacements pour passer des noms de polices TCPDF aux noms SVGFont
        $fontReplacements = [
            '"Amatic Bold"' => '"Amatic"',
            '"Baroque Script"' => '"Baroque"',
            '"Libre Baskerville"' => '"Baskerville"',
            '"Birds of Paradise Personal use"' => '"Birds of Paradise"',
            '"Comic Sans MS"' => '"Comic Sans"',
            '"Cooper Black"' => '"Cooper"',
            '"Dancing Script OT"' => '"Dancing"',
            '"Edwardian Script ITC"' => '"Edwardian"',
            '"Freestyle Script"' => '"FreestyleScript"',
            '"Harrington (Plain):001.001"' => '"Harrington"',
            '"KentuckyFriedChickenFont"' => '"KentuckyFriedChicken"',
            '"QK Marisa"' => '"Lucida Handwriting"',
            '"Not Just Groovy"' => '"Not just Groovy"',
            '"Old English Text MT"' => '"Old English"',
            '"Script MT Bold"' => '"Script MT Bold"',
            '"phitradesign INK"' => '"Phitradesign Ink"',
        ];
        
        // Remplacer tous les noms de polices
        foreach ($fontReplacements as $search => $replace) {
            $svgContent = str_replace($search, $replace, $svgContent);
        }
        
        // Remplacer les espaces doubles par des espaces insécables
        $svgContent = str_replace('  ', '&#160;&#160;', $svgContent);
        
        return $svgContent;
    }
    
    /**
     * Convertit les textes en chemins dans un fichier SVG en utilisant SVGFont
     *
     * @param string $svgContent Le contenu du fichier SVG
     * @return string Le contenu SVG avec les textes convertis en chemins
     */
    function convertSvgTextToPaths($svgContent) {
        // S'assurer que la classe SVGFont est disponible
        $svgFontPath = BP . '/lib/internal/SVGFont_Claude/SVGFont.php';
        if (!file_exists($svgFontPath)) {
            // Si la classe n'est pas disponible, renvoyer le contenu original
            error_log('Fichier SVGFont introuvable: ' . $svgFontPath);
            return $svgContent;
        }
        
        require_once $svgFontPath;
        
        try {
            // Adapter les synonymes de polices pour qu'ils fonctionnent avec notre nomenclature
            $this->setupSVGFontSynonyms();
            
            // Convertir le SVG
            $convertedSvg = \SVGFont::convertSVGTextToPaths($svgContent);
            return $convertedSvg;
        } catch (\Exception $e) {
            // En cas d'erreur, enregistrer l'erreur et retourner le SVG original
            error_log('Erreur lors de la conversion SVG: ' . $e->getMessage());
            return $svgContent;
        }
    }
    
    /**
     * Configure les synonymes de polices pour SVGFont
     */
    private function setupSVGFontSynonyms() {
        // S'assurer que la classe est chargée
        if (!class_exists('\SVGFont')) {
            return;
        }
        
        // Ajouter des synonymes supplémentaires spécifiques à notre application
        $additionalSynonyms = [
            'amatic bold' => 'Amatic',
            'baroque script' => 'Baroque',
            'libre baskerville' => 'Baskerville',
            'birds of paradise personal use' => 'Birds of Paradise',
            'comic sans ms' => 'Comic Sans',
            'cooper black' => 'Cooper',
            'dancing script ot' => 'Dancing',
            'edwardian script itc' => 'Edwardian',
            'freestyle script' => 'FreestyleScript',
            'harrington (plain):001.001' => 'Harrington',
            'kentuckyfriedchickenfont' => 'KentuckyFriedChicken',
            'qk marisa' => 'Lucida Handwriting',
            'not just groovy' => 'Not just Groovy',
            'old english text mt' => 'Old English',
            'script mt bold' => 'Script MT Bold',
            'phitradesign ink' => 'Phitradesign Ink',
        ];
        
        if (method_exists('\SVGFont', 'addFontSynonyms')) {
            \SVGFont::addFontSynonyms($additionalSynonyms);
        }
    }

    
    
    
    /**
     * Traite un SVG pour le préparer pour TCPDF (standardise les polices, vectorise le texte, nettoie)
     * 
     * @param string $svgContent Contenu SVG original
     * @return string SVG préparé pour TCPDF
     */
    function prepareSvgForTcpdf($svgContent) {
    error_log("AVANT: " . substr($svgContent, 0, 500));

        // 1. Standardiser les noms de polices pour qu'ils correspondent à ceux de SVGFont
        $svgContent = $this->standardizeFonts($svgContent);
        
        // 2. Convertir les textes en chemins avec SVGFont
        $svgContent = $this->convertSvgTextToPaths($svgContent);
        
        // 3. Nettoyer le SVG pour TCPDF
        $svgContent = $this->cleanSvgForTcpdf($svgContent);
        error_log("APRES: " . substr($svgContent, 0, 500));
        return $svgContent;
    }
    
     /**
     * Nettoie un SVG pour le rendre compatible avec TCPDF
     * 
     * @param string $svgContent Contenu SVG original
     * @return string SVG nettoyé
     */
    function cleanSvgForTcpdf($svgContent) {
        // 1. Gérer le commentaire problématique contenant une déclaration XML
        $svgContent = preg_replace('/<!--\?xml.*?-->/', '', $svgContent);
        
        // 2. Nettoyer la déclaration XML initiale
        $svgContent = preg_replace('/<\?xml[^>]*\?>/', '', $svgContent);
        
        // 3. Ajouter une déclaration XML propre au début
        $svgContent = '<?xml version="1.0" encoding="UTF-8" standalone="no" ?>' . "\n" . $svgContent;
        
        // 4. Supprimer les autres commentaires qui pourraient causer des problèmes
        $svgContent = preg_replace('/<!--.*?-->/s', '', $svgContent);
        
        // 5. Optimisations supplémentaires pour TCPDF
        $svgContent = str_replace("\t", ' ', $svgContent);
        $svgContent = preg_replace('/\s+/u', ' ', $svgContent);
        
        return $svgContent;
    }
    
}