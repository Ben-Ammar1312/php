<?php

abstract class Operation
{
    protected $numOp;
    protected $dateOp; // \DateTime object in PHP
    protected $agentId;
    protected $fraisOp;
    protected $montant;
    protected $cnx;
    protected  $numCpt;

    /**
     * @return mixed
     */
    public function getCompte()
    {
        return $this->numCpt;
    }

    /**
     * @param mixed $numCpt
     */
    public function setCompte($numCpt): void
    {
        $this->numCpt = $numCpt;
    }

    public abstract function ajout();

    public function __construct($cnx, $numOp = 0, $dateOp = null, $agentId = 0, $fraisOp = 0.0, $montant = 0.0)
    {
        $this->numOp = $numOp;
        $this->dateOp = $dateOp ? new \DateTime($dateOp) : null;
        $this->agentId = $agentId;
        $this->fraisOp = $fraisOp;
        $this->montant = $montant;
        $this->cnx = $cnx;
    }

    public abstract function getType();

    public function getNumOp() { return $this->numOp; }
    public function setNumOp($numOp) { $this->numOp = $numOp; }
    public function getDateOp() { return $this->dateOp ? $this->dateOp->format('Y-m-d H:i:s') : null; }
    public function setDateOp($dateOp) { $this->dateOp = new \DateTime($dateOp); }
    public function getAgentId() { return $this->agentId; }
    public function setAgentId($agentId) { $this->agentId = $agentId; }
    public function getFraisOp() { return $this->fraisOp; }
    public function setFraisOp($fraisOp) { $this->fraisOp = $fraisOp; }
    public function getMontant() { return $this->montant; }
    public function setMontant($montant) { $this->montant = $montant; }

    public static function getOperationsByCompte($cnx, $accountNumber) {
        $stmt = $cnx->prepare("SELECT * FROM operation WHERE num_cpt = :accountNumber");
        $stmt->bindParam(':accountNumber', $accountNumber);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $operations = [];
        foreach ($rows as $row) {
            // Check if numOp exists in the deposits table
            $depositStmt = $cnx->prepare("SELECT 1 FROM virement WHERE num_op = :numOp");
            $depositStmt->bindParam(':numOp', $row['num_op']);
            $depositStmt->execute();
            if ($depositStmt->fetch()) {
                $operations[] = new Virement($cnx, $row['num_op'], $row['date_op'], $row['agent_id'], $row['frais_op'], $row['montant']);
                continue;
            }
            $depositStmt = $cnx->prepare("SELECT 1 FROM versement WHERE num_op = :numOp");
            $depositStmt->bindParam(':numOp', $row['num_op']);
            $depositStmt->execute();
            if ($depositStmt->fetch()) {
                $operations[] = new Versement($cnx, $row['num_op'], $row['date_op'], $row['agent_id'], $row['frais_op'], $row['montant']);
                continue;
            }
            $depositStmt = $cnx->prepare("SELECT 1 FROM retrait WHERE num_op = :numOp");
            $depositStmt->bindParam(':numOp', $row['num_op']);
            $depositStmt->execute();
            if ($depositStmt->fetch()) {
                $operations[] = new Retrait($cnx, $row['num_op'], $row['date_op'], $row['agent_id'], $row['frais_op'], $row['montant']);
                continue;
            }
            $depositStmt = $cnx->prepare("SELECT 1 FROM placement WHERE num_op = :numOp");
            $depositStmt->bindParam(':numOp', $row['num_op']);
            $depositStmt->execute();
            if ($depositStmt->fetch()) {
                $operations[] = new Placement($cnx, $row['num_op'], $row['date_op'], $row['agent_id'], $row['frais_op'], $row['montant']);
                continue;
            }
            $depositStmt = $cnx->prepare("SELECT 1 FROM paiementCredit WHERE num_op = :numOp");
            $depositStmt->bindParam(':numOp', $row['num_op']);
            $depositStmt->execute();
            if ($depositStmt->fetch()) {
                $operations[] = new PaiementCredit($cnx, $row['num_op'], $row['date_op'], $row['agent_id'], $row['frais_op'], $row['montant']);
                continue;
            }
            throw new Exception("Unknown operation type for numOp: " . $row['numOp']);
        }
        return $operations;
    }

    public static function getAllOperations($cnx) {
        $stmt = $cnx->query("SELECT * FROM operation");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $operations = [];
        foreach ($rows as $row) {
            // Check if numOp exists in the virement table
            $operationTypeStmt = $cnx->prepare("SELECT 1 FROM virement WHERE num_op = :numOp");
            $operationTypeStmt->bindParam(':numOp', $row['num_op']);
            $operationTypeStmt->execute();
            if ($operationTypeStmt->fetch()) {
                $operations[] = new Virement($cnx, $row['num_op'], $row['date_op'], $row['agent_id'], $row['frais_op'], $row['montant']);
                continue;
            }

            // Check if numOp exists in the versement table
            $operationTypeStmt = $cnx->prepare("SELECT 1 FROM versement WHERE num_op = :numOp");
            $operationTypeStmt->bindParam(':numOp', $row['num_op']);
            $operationTypeStmt->execute();
            if ($operationTypeStmt->fetch()) {
                $operations[] = new Versement($cnx, $row['num_op'], $row['date_op'], $row['agent_id'], $row['frais_op'], $row['montant']);
                continue;
            }

            // Check if numOp exists in the retrait table
            $operationTypeStmt = $cnx->prepare("SELECT 1 FROM retrait WHERE num_op = :numOp");
            $operationTypeStmt->bindParam(':numOp', $row['num_op']);
            $operationTypeStmt->execute();
            if ($operationTypeStmt->fetch()) {
                $operations[] = new Retrait($cnx, $row['num_op'], $row['date_op'], $row['agent_id'], $row['frais_op'], $row['montant']);
                continue;
            }

            // Check if numOp exists in the placement table
            $operationTypeStmt = $cnx->prepare("SELECT 1 FROM placement WHERE num_op = :numOp");
            $operationTypeStmt->bindParam(':numOp', $row['num_op']);
            $operationTypeStmt->execute();
            if ($operationTypeStmt->fetch()) {
                $operations[] = new Placement($cnx, $row['num_op'], $row['date_op'], $row['agent_id'], $row['frais_op'], $row['montant']);
                continue;
            }

            // Check if numOp exists in the paiementCredit table
            $operationTypeStmt = $cnx->prepare("SELECT 1 FROM paiementCredit WHERE num_op = :numOp");
            $operationTypeStmt->bindParam(':numOp', $row['num_op']);
            $operationTypeStmt->execute();
            if ($operationTypeStmt->fetch()) {
                $operations[] = new PaiementCredit($cnx, $row['num_op'], $row['date_op'], $row['agent_id'], $row['frais_op'], $row['montant']);
                continue;
            }

            // If no known operation type is found, throw an exception
            throw new Exception("Unknown operation type for numOp: " . $row['num_op']);
        }

        return $operations;
    }





}
?>
