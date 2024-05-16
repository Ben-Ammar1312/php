<?php
class Compte
{
    private $numCpt;
    private $montantGlobal;
    private $montantInit;
    private $codeCli;
    private $cnx;

    public function __construct($cnx, $numCpt = 0, $montantGlobal = 0.0, $montantInit = 0.0, $codeCli = 0)
    {
        $this->cnx = $cnx;
        $this->numCpt = $numCpt;
        $this->montantGlobal = $montantGlobal;
        $this->montantInit = $montantInit;
        $this->codeCli = $codeCli;
    }

    public static function findByNumCpt(PDO $cnx, mixed $numCpt)
    {
        $sql = "SELECT * FROM compte WHERE num_cpt = :numCpt";
        $stmt = $cnx->prepare($sql);
        $stmt->bindParam(':numCpt', $numCpt);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new self(
                $cnx,
                $row['num_cpt'],
                $row['montant_global'],
                $row['montant_init'],
                $row['code_cli']
            );
        }

        return null;
    }

    public function getNumCpt() { return $this->numCpt; }
    public function setNumCpt($numCpt) { $this->numCpt = $numCpt; }
    public function getMontantGlobal() { return $this->montantGlobal; }
    public function setMontantGlobal($montantGlobal) { $this->montantGlobal = $montantGlobal; }
    public function getMontantInit() { return $this->montantInit; }
    public function setMontantInit($montantInit) { $this->montantInit = $montantInit; }
    public function getCodeCli() { return $this->codeCli; }
    public function setCodeCli($codeCli) { $this->codeCli = $codeCli; }

    public function ajout()
    {
        $sql = "INSERT INTO compte (montant_global, montant_init, code_cli) VALUES (:montantGlobal, :montantInit, :codeCli)";
        $statement = $this->cnx->prepare($sql);
        $statement->execute([
            ':montantGlobal' => $this->montantGlobal,
            ':montantInit' => $this->montantInit,
            ':codeCli' => $this->codeCli
        ]);
        $this->numCpt = $this->cnx->lastInsertId();
    }

    public function update()
    {
        $sql = "UPDATE compte SET montant_global=:montantGlobal, montant_init=:montantInit, code_cli=:codeCli WHERE num_cpt=:numCpt";
        $statement = $this->cnx->prepare($sql);
        $statement->execute([
            ':montantGlobal' => $this->montantGlobal,
            ':montantInit' => $this->montantInit,
            ':codeCli' => $this->codeCli,
            ':numCpt' => $this->numCpt
        ]);
    }

    public function delete()
    {
        $tmp= $this->codeCli;
        $sql = "DELETE FROM compte WHERE num_cpt=:numCpt";
        $statement = $this->cnx->prepare($sql);
        $statement->execute([':numCpt' => $this->numCpt]);

    }

    public static function findAll($cnx)
    {
        $sql = "SELECT * FROM compte";
        $statement = $cnx->prepare($sql);
        $statement->execute();
        $comptes = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $comptes[] = new self(
                $cnx,
                $row['num_cpt'],
                $row['montant_global'],
                $row['montant_init'],
                $row['code_cli']
            );
        }
        return $comptes;
    }

    public function getCompteByClientCode($codeCli)
    {
        $stmt = $this->cnx->prepare("SELECT * FROM compte WHERE code_cli = :clientCode");
        $stmt->bindParam(':clientCode', $codeCli);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new self(
                $this->cnx,
                $row['num_cpt'],
                $row['montant_global'],
                $row['montant_init'],
                $row['code_cli']
            );
        }

        return null;
    }
}
?>
