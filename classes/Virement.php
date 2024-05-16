<?php


class Virement extends Operation
{
    private $destinataire;
    private $source;

    public function __construct($cnx,$numOp = 0, $dateOp = null, $agentId = 0, $fraisOp = 0.0, $montant = 0.0, $destinataire = 0, $source = 0)
    {
        parent::__construct($cnx,$numOp, $dateOp, $agentId, $fraisOp, $montant);
        $this->destinataire = $destinataire;
        $this->source = $source;
    }

    public function getDestinataire() { return $this->destinataire; }
    public function setDestinataire($destinataire) { $this->destinataire = $destinataire; }
    public function getSource() { return $this->source; }
    public function setSource($source) { $this->source = $source; }


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
        $sql = "INSERT INTO virement (num_op) VALUES (:numOp)";
        $stmt = $this->cnx->prepare($sql);
        $stmt->execute([':numOp' => $this->numOp]);
    }

    public function update($cnx)
    {
        $sql = "UPDATE operation SET date_op=:dateOp, agent_id=:agentId, frais_op=:fraisOp, montant=:montant, dest=:dest, src=:src WHERE num_op=:numOp";
        $statement = $cnx->prepare($sql);
        $statement->execute([
            ':dateOp' => $this->getDateOp(),
            ':agentId' => $this->agentId,
            ':fraisOp' => $this->fraisOp,
            ':montant' => $this->montant,
            ':dest' => $this->destinataire,
            ':src' => $this->source,
            ':numOp' => $this->numOp
        ]);
    }

    public function delete($cnx)
    {
        $sql = "DELETE FROM Virement WHERE num_op=:numOp";
        $statement = $cnx->prepare($sql);
        $statement->execute([':numOp' => $this->numOp]);
        $sql = "DELETE FROM operation WHERE num_op=:numOp";
        $statement = $cnx->prepare($sql);
        $statement->execute([':numOp' => $this->numOp]);
    }

    public static function findAll($cnx)
    {
        $sql = "SELECT * FROM Virement";
        $statement = $cnx->prepare($sql);
        $statement->execute();
        $virements = [];
        while ($row = $statement->fetch()) {
            $virements[] = new self(
                $row['num_op'],
                $row['date_op'],
                $row['agent_id'],
                $row['frais_op'],
                $row['montant'],
                $row['dest'],
                $row['src']
            );
        }
        return $virements;
    }

    public function getType()
    {
        return "virement";
    }
}
