<?php
require_once __DIR__ . '/../model/Model.php';

class ModelClimatique {

    public function getMesuresMoyennesParDateEtParametre($date_choisie, $parametre) {
        $resultats = [];
    
        try {
            // Valider la date fournie par l'utilisateur
            $dateChoisieObj = new DateTime($date_choisie);
            $dateStr = $dateChoisieObj->format('Y-m-d');
    
            // Ajouter 1 jour pour définir la plage de dates
            $dateFinObj = clone $dateChoisieObj;
            $dateFinObj->modify('+1 day');
            $dateFinStr = $dateFinObj->format('Y-m-d');
    
            $limit = 100;
            $offset = 0;
            $stations = []; 
    
            while (true) {
                // Construire la clause "where" avec une plage de dates
                $where_clause = "date>='$dateStr' AND date<'$dateFinStr'";
                $where_encoded = urlencode($where_clause);
    
                $url = "https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/donnees-synop-essentielles-omm/records?where=$where_encoded&order_by=date&limit=$limit&offset=$offset";
    
                // Récupérer les données de l'API
                $response = file_get_contents($url);
                if ($response === false) {
                    throw new Exception("Erreur lors de la récupération des données depuis l'API.");
                }
    
                $data = json_decode($response, true);
    
                // Vérifier s'il y a des résultats dans cette page
                if (!isset($data['results']) || !is_array($data['results']) || count($data['results']) === 0) {
                    break; // Si aucune donnée, sortir de la boucle
                }
    
                // Parcourir les résultats de la page actuelle
                foreach ($data['results'] as $result) {
                    // Récupérer le numéro de station et la valeur du paramètre
                    $num_station = $result['numer_sta'] ?? null;
                    $nom_station = $result['nom'] ?? null;
                    $valeur_parametre = $result[$parametre] ?? null;
                    $latitude = $result['coordonnees']['lat'] ?? null; 
                    $longitude = $result['coordonnees']['lon'] ?? null;

                    if ($num_station && $valeur_parametre !== null) {
                        // Ajouter la mesure au tableau de la station correspondante
                        if (!isset($stations[$num_station])) {
                            $stations[$num_station] = [
                                'total' => 0,
                                'compteur' => 0,
                                'nom_station' =>$nom_station,
                                'latitude' => $latitude,
                                'longitude' => $longitude,
                            ];
                        }
    
                        $stations[$num_station]['total'] += $valeur_parametre;
                        $stations[$num_station]['compteur']++;
                    }
                }
    
                // Passer à la page suivante
                $offset += $limit;
            }
    
            // Calculer les moyennes pour chaque station
            foreach ($stations as $station_id => $data_station) {
                $moyenne = ($data_station['compteur'] > 0) 
                    ? round($data_station['total'] / $data_station['compteur'], 2) 
                    : null;
    
                    $resultats[$station_id] = [
                        'nom_station' => $data_station['nom_station'],
                        'moyenne' => $moyenne,
                        'latitude' => $data_station['latitude'],
                        'longitude' => $data_station['longitude'],
                    ];
            }
        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    
        return $resultats;
    }

    //Fonction pour calculer la moyenne pour la date de début et la date de fin
    public function getMesuresMoyennesParDebutEtFin($date_debut, $date_fin, $parametre) {
        // Moyenne pour la date de début
        $resultats_debut = $this->getMesuresMoyennesParDateEtParametre($date_debut, $parametre); 
        // Moyenne pour la date de fin
        $resultats_fin = $this->getMesuresMoyennesParDateEtParametre($date_fin, $parametre); 
    
        // Calculer la variation entre les moyennes de début et de fin
        $resultats = [];
    
        foreach ($resultats_debut as $station_id => $data_debut) {
            $data_fin = $resultats_fin[$station_id] ?? null;
    
            $moyenne_debut = $data_debut['moyenne'] ?? null;
            $moyenne_fin = $data_fin['moyenne'] ?? null;
    
            $variation = null;
            if ($moyenne_debut !== null && $moyenne_fin !== null) {
                $variation = round($moyenne_fin - $moyenne_debut, 2);
            }
    
            $resultats[$station_id] = [
                'nom_station' => $data_debut['nom_station'],  
                'moyenne_debut' => $moyenne_debut,
                'moyenne_fin' => $moyenne_fin,
                'variation' => $variation,
                'latitude' => $data_debut['latitude'] ?? null, 
                'longitude' => $data_debut['longitude'] ?? null, 
            ];
        }
        return $resultats;
    }    
}
?>

