<?php


class Retrait extends Operation
{
    public function __construct($cnx,$numOp = 0, $dateOp = null, $agentId = 0, $fraisOp = 0.0, $montant = 0.0)
    {
        parent::__construct($cnx,$numOp, $dateOp, $agentId, $fraisOp, $montant);
    }


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
        $sql = "INSERT INTO retrait (num_op) VALUES (:numOp)";
        $stmt = $this->cnx->prepare($sql);
        $stmt->execute([':numOp' => $this->numOp]);
    }

    public function update($cnx)
    {
        $sql = "UPDATE operation SET date_op=:dateOp, agent_id=:agentId, frais_op=:fraisOp, montant=:montant WHERE num_op=:numOp";
        $statement = $cnx->prepare($sql);
        $statement->execute([
            ':dateOp' => $this->getDateOp(),
            ':agentId' => $this->agentId,
            ':fraisOp' => $this->fraisOp,
            ':montant' => $this->montant,
            ':numOp' => $this->numOp
        ]);
    }

    public function delete($cnx)
    {
        $sql = "DELETE FROM retrait WHERE num_op=:numOp";
        $statement = $cnx->prepare($sql);
        $statement->execute([':numOp' => $this->numOp]);
        $sql = "DELETE FROM operation WHERE num_op=:numOp";
        $statement = $cnx->prepare($sql);
        $statement->execute([':numOp' => $this->numOp]);
    }

    public static function findAll($cnx)
    {
        $sql = "SELECT * FROM retrait";
        $statement = $cnx->prepare($sql);
        $statement->execute();
        $retraits = [];
        while ($row = $statement->fetch()) {
            $retraits[] = new self(
                $row['num_op'],
                $row['date_op'],
                $row['agent_id'],
                $row['frais_op'],
                $row['montant']
            );
        }
        return $retraits;
    }

    public function getType()
    {

        return "Retrait";
    }
}
