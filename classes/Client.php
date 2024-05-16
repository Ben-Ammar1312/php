<?php
class Client
{
    private $codeCli;
    private $nomCli;
    private $preCli;
    private $dateNaissance; // DateTime object
    private $numTel;
    private $emailCli;
    private $adrCli;
    private $cnx;

    public function __construct($cnx, $codeCli = 0, $nomCli = '', $preCli = '', $dateNaissance = null, $numTel = '', $emailCli = '', $adrCli = '')
    {
        $this->cnx = $cnx;
        $this->codeCli = $codeCli;
        $this->nomCli = $nomCli;
        $this->preCli = $preCli;
        $this->dateNaissance = $dateNaissance ? new \DateTime($dateNaissance) : null;
        $this->numTel = $numTel;
        $this->emailCli = $emailCli;
        $this->adrCli = $adrCli;
    }

    public function getCodeCli() { return $this->codeCli; }
    public function setCodeCli($codeCli) { $this->codeCli = $codeCli; }
    public function getNomCli() { return $this->nomCli; }
    public function setNomCli($nomCli) { $this->nomCli = $nomCli; }
    public function getPreCli() { return $this->preCli; }
    public function setPreCli($preCli) { $this->preCli = $preCli; }
    public function getNumTel() { return $this->numTel; }
    public function setNumTel($numTel) { $this->numTel = $numTel; }
    public function getDateNaissance() { return $this->dateNaissance; }
    public function setDateNaissance($dateNaissance) { $this->dateNaissance = $dateNaissance; }
    public function getEmailCli() { return $this->emailCli; }
    public function setEmailCli($emailCli) { $this->emailCli = $emailCli; }
    public function getAdrCli() { return $this->adrCli; }
    public function setAdrCli($adrCli) { $this->adrCli = $adrCli; }
    public function getCnx() { return $this->cnx; }
    public function setCnx($cnx) { $this->cnx = $cnx; }

    public function ajout()
    {
        $sql = "INSERT INTO client (nom_cli, pre_cli, date_naissance, num_tel, email_cli, adr_cli) VALUES (:nomCli, :preCli, :dateNaissance, :numTel, :emailCli, :adrCli)";
        $statement = $this->cnx->prepare($sql);
        $statement->execute([
            ':nomCli' => $this->nomCli,
            ':preCli' => $this->preCli,
            ':dateNaissance' => $this->dateNaissance->format('Y-m-d'),
            ':numTel' => $this->numTel,
            ':emailCli' => $this->emailCli,
            ':adrCli' => $this->adrCli
        ]);
        $this->codeCli = $this->cnx->lastInsertId();
    }

    public function update()
    {
        $sql = "UPDATE client SET nom_cli=:nomCli, pre_cli=:preCli, date_naissance=:dateNaissance, num_tel=:numTel, email_cli=:emailCli, adr_cli=:adrCli WHERE code_cli=:codeCli";
        $statement = $this->cnx->prepare($sql);
        $statement->execute([
            ':nomCli' => $this->nomCli,
            ':preCli' => $this->preCli,
            ':dateNaissance' => $this->dateNaissance->format('Y-m-d'),
            ':numTel' => $this->numTel,
            ':emailCli' => $this->emailCli,
            ':adrCli' => $this->adrCli,
            ':codeCli' => $this->codeCli
        ]);
    }

    public function delete()
    {
        $sql = "DELETE FROM client WHERE code_cli=:codeCli";
        $statement = $this->cnx->prepare($sql);
        $statement->execute([':codeCli' => $this->codeCli]);
    }


    public static function findAll($cnx)
    {
        $sql = "SELECT * FROM client";
        $statement = $cnx->prepare($sql);
        $statement->execute();
        $clients = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $client = new self(
                $cnx,
                $row['code_cli'],
                $row['nom_cli'],
                $row['pre_cli'],
                $row['date_naissance'],
                $row['num_tel'],
                $row['email_cli'],
                $row['adr_cli']
            );
            $clients[] = $client;
        }
        return $clients;
    }

    public function getClient($id)
    {
        $stmt = $this->cnx->prepare("SELECT * FROM client WHERE code_cli = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new self(
                $this->cnx,
                $row['code_cli'],
                $row['nom_cli'],
                $row['pre_cli'],
                $row['date_naissance'],
                $row['num_tel'],
                $row['email_cli'],
                $row['adr_cli']
            );
        }

        return null;
    }
}
?>
