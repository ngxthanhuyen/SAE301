<?php
require_once __DIR__ . '/../model/Model.php';

class ModelAlerte {

    private $pdo;

    public function __construct() {
        $this->pdo = Model::getInstance()->getPdo();
    }

    //Méthode pour récupérer les mesures depuis l'API et vérifier les alertes
    public function verifierAlerte($date_debut, $date_fin, $parametre, $seuil, $station) {
        $url = "https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/donnees-synop-essentielles-omm/records";

        //Encodage des dates et de la station dans les paramètres passés en URL
        $parametres = urlencode("date>='$date_debut' AND date<='$date_fin' AND numer_sta='$station'");
        $limit = 100; 
        $offset = 0; 
        $alertes = [];

        // Analyser le seuil pour détecter un opérateur (<, >, <=, >=)
        if (preg_match('/^(?<operator>[<>]=?)?(?<value>-?\d+(\.\d+)?)$/', $seuil, $matches)) {
            // Opérateur par défaut(si absent)
            $operator = $matches['operator'] ?? '='; 
            // Valeur numérique du seuil
            $seuil_value = (float) $matches['value']; 
        } else {
            throw new Exception("Le format du seuil est invalide. Utilisez <valeur, >valeur, <=valeur ou >=valeur.");
        }

        while (true) {
            //On construit l'URL avec pagination
            $api_url = "$url?where=$parametres&limit=$limit&offset=$offset";

            //On récupère les données de l'API via cURL
            $response = $this->fetchDataWithCurl($api_url);
            
            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Erreur de parsing JSON : " . json_last_error_msg());
            }

            // On vérifie s'il y a des résultats
            if (!isset($data['results']) || !is_array($data['results']) || count($data['results']) === 0) {
                // Si plus de résultats, on sort de la boucle
                break; 
            }
            // Tableau associatif pour mapper les codes de paramètres à des noms lisibles
            $type_parametre_map = [
                'tc' => 'Température',
                'pres' => 'Pression',
                'vv' => 'Visibilité',
                'u' => 'Humidité',
                'ff' => 'Vent',
                'rr1' => 'Précipitation'
            ];

            // Parcourir les résultats de la page actuelle
            foreach ($data['results'] as $result) {
                $num_station = $result['numer_sta'] ?? null;
                $nom_station = $result['nom'] ?? null;
                $valeur_parametre = $result[$parametre] ?? null; 
                $date_heure = $result['date'] ?? null; 


                if ($num_station && $nom_station && $valeur_parametre !== null && $date_heure) {
                    //Séparer la date & l'heure
                    list($date, $heure_avec_timezone) = explode('T', $date_heure);
                    list($heure, $timezone) = explode('+', $heure_avec_timezone);

                    //On ne garde que l'heure au format HH:mm
                    $heure = substr($heure, 0, 5); //à partir de l'index 0, la fonction va extraire les 5 premiers caractères

                    if (is_numeric($valeur_parametre)) {
                        // Convertir en float pour s'assurer que c'est un nombre
                        $valeur_parametre = (float)$valeur_parametre;
                        
                        // Vérifier si le nombre est un entier
                        if (floor($valeur_parametre) == $valeur_parametre) {
                            // Si c'est un entier, le convertir en entier pour supprimer les décimales
                            $valeur_parametre = (int)$valeur_parametre;
                        } else {
                            // Si c'est un float, arrondir à deux décimales
                            $valeur_parametre = round($valeur_parametre, 2);
                        }
                    }                    
                    // Récupérer le type de paramère avec son nom lisible
                    $type_parametre = isset($type_parametre_map[$parametre]) ? $type_parametre_map[$parametre] : $parametre;

                    // On vérifie si la valeur respecte le seuil 
                    if ($this->compareSeuil($valeur_parametre, $operator, $seuil_value)) {
                        // Ajouter l'alerte si la condition est remplie
                        $alertes[] = [
                            'station' => $nom_station,
                            'num_station' => $num_station, 
                            'valeur' => $valeur_parametre,
                            'date' => $date,
                            'heure' => $heure,
                            'parametre' => $type_parametre
                        ];
                    }
                }
            }

            // Passer à la page suivante
            $offset += $limit;
        }

        return $alertes;
    }

    // Méthode pour comparer une valeur avec un seuil dynamique
    private function compareSeuil($valeur, $operator, $seuil) {
        // Arrondir à 2 décimales avant la comparaison
        if (is_float($valeur)) {
            $valeur = number_format($valeur, 2, '.', '');
        }
    
        $seuil_arrondi = number_format($seuil, 2, '.', '');
        // On définit l'opérateur par défaut à '=' si non spécifié
        if (empty($operator)) {
            $operator = '=';
        }
    
        switch ($operator) {
            case '>':
                return $valeur > $seuil_arrondi;
            case '>=':
                return $valeur >= $seuil_arrondi;
            case '<':
                return $valeur < $seuil_arrondi;
            case '<=':
                return $valeur <= $seuil_arrondi;
            case '=':
                return $valeur == $seuil_arrondi;  
            default:
                throw new Exception("Opérateur non valide");
        }
    }


    // Méthode pour récupérer les données avec cURL
    private function fetchDataWithCurl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception("Erreur cURL: " . curl_error($ch));
        }
        curl_close($ch);
        return $response;
    }
    // Méthode pour sauvegarder une alerte
    public function sauvegarderAlerte($user_id, $station, $parametre, $valeur, $date_alerte, $heure_alerte) {
        // Vérifier si une alerte identique existe déjà
        $query = "SELECT COUNT(*) FROM alertes 
                  WHERE station = :station 
                  AND user_id = :user_id
                  AND parametre = :parametre 
                  AND valeur = :valeur 
                  AND date_alerte = :date_alerte 
                  AND heure_alerte = :heure_alerte";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':station', $station);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':parametre', $parametre);
        $stmt->bindParam(':valeur', $valeur);
        $stmt->bindParam(':date_alerte', $date_alerte);
        $stmt->bindParam(':heure_alerte', $heure_alerte);
        $stmt->execute();
    
        // Si une alerte identique existe déjà, on ne fait rien
        if ($stmt->fetchColumn() > 0) {
            return false; 
        }
    
        // Sinon, on insère la nouvelle alerte
        $query = "INSERT INTO alertes (user_id, station, parametre, valeur, date_alerte, heure_alerte) 
                  VALUES (:user_id, :station, :parametre, :valeur, :date_alerte, :heure_alerte)";
        $stmt = $this->pdo->prepare($query);
        
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':station', $station);
        $stmt->bindParam(':parametre', $parametre);
        $stmt->bindParam(':valeur', $valeur);
        $stmt->bindParam(':date_alerte', $date_alerte);
        $stmt->bindParam(':heure_alerte', $heure_alerte);
    
        return $stmt->execute(); 
    }    
}
?>
