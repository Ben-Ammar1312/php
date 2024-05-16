<?php

class Placement extends Operation
{
    private $interet;
    private $nouveauMontant;

    public function __construct($cnx,$numOp = 0, $dateOp = null, $agentId = 0, $fraisOp = 0.0, $montant = 0.0, $interet = 0.0, $nouveauMontant = 0.0)
    {
        parent::__construct($cnx,$numOp, $dateOp, $agentId, $fraisOp, $montant);
        $this->interet = $interet;
        $this->nouveauMontant = $nouveauMontant;
    }

    // Getters and Setters
    public function getInteret() { return $this->interet; }
    public function setInteret($interet) { $this->interet = $interet; }
    public function getNouveauMontant() { return $this->nouveauMontant; }
    public function setNouveauMontant($nouveauMontant) { $this->nouveauMontant = $nouveauMontant; }


    public function ajout()
    {
        $sql = "INSERT INTO operation (date_op, agent_id, frais_op, montant, num_cpt) VALUES (:dateOp, :agentId, :fraisOp, :montant, :numCpt)";
        $stmt = $this->cnx->prepare($sql);
        $stmt->execute([
            ':dateOp' => $this->getDateOp(),
            ':agentId' => $this->agentId,
            ':fraisOp' => $this->fraisOp,
            ':montant' => $this->montant,
            ':numCpt' => $this->numCpt
        ]);
        $this->numOp = $this->cnx->lastInsertId();

        // Now insert into versement table
        $sql = "INSERT INTO placement (num_op, interet,nouveau_montant) VALUES (:numOp,:interet,:nouveauMontant)";
        $stmt = $this->cnx->prepare($sql);
        $stmt->execute([':numOp' => $this->numOp, ':interet' => 0.07,':nouveauMontant' => $this->nouveauMontant]);
    }

    public function update($cnx)
    {
        $sql = "UPDATE operation SET date_op=:dateOp, agent_id=:agentId, frais_op=:fraisOp, montant=:montant, interet=:interet, nouveau_montant=:nouveauMontant WHERE num_op=:numOp";
        $statement = $cnx->prepare($sql);
        $statement->execute([
            ':date_op' => $this->getDateOp(),
            ':agent_id' => $this->agentId,
            ':frais_op' => $this->fraisOp,
            ':montant' => $this->montant,
            ':interet' => $this->interet,
            ':nouveau_montant' => $this->nouveauMontant,
            ':num_op' => $this->numOp
        ]);
    }

    public function delete($cnx)
    {
        $sql = "DELETE FROM Placement WHERE numOp=:numOp";
        $statement = $cnx->prepare($sql);
        $statement->execute([':numOp' => $this->numOp]);
        $sql = "DELETE FROM operation WHERE numOp=:numOp";
        $statement = $cnx->prepare($sql);
        $statement->execute([':numOp' => $this->numOp]);
    }

    public static function findAll($cnx)
    {
        $sql = "SELECT * FROM Placement";
        $statement = $cnx->prepare($sql);
        $statement->execute();
        $placements = [];
        while ($row = $statement->fetch()) {
            $placements[] = new self(
                $row['numOp'],
                $row['dateOp'],
                $row['agentId'],
                $row['fraisOp'],
                $row['montant'],
                $row['interet'],
                $row['nouveauMontant']
            );
        }
        return $placements;
    }

    public function getType()
    {
        return "Placement";
    }
}
