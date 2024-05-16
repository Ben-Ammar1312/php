<?php


class Credit
{
    private $numCre;
    private $dateCre; // \DateTime object in PHP
    private $montCre;
    private $codeCli;
    private $numCpt;
    private $filesPath;
    private $status;

    public function __construct($numCre = 0, $dateCre = null, $montCre = 0, $codeCli = 0, $numCpt = 0, $filesPath = '', $status = '')
    {
        $this->numCre = $numCre;
        $this->dateCre = $dateCre ? new \DateTime($dateCre) : null;
        $this->montCre = $montCre;
        $this->codeCli = $codeCli;
        $this->numCpt = $numCpt;
        $this->filesPath = $filesPath;
        $this->status = $status;
    }

    public function getNumCre() { return $this->numCre; }
    public function setNumCre($numCre) { $this->numCre = $numCre; }
    public function getDateCre() { return $this->dateCre ? $this->dateCre->format('Y-m-d H:i:s') : null; }
    public function setDateCre($dateCre) { $this->dateCre = new \DateTime($dateCre); }
    public function getMontCre() { return $this->montCre; }
    public function setMontCre($montCre) { $this->montCre = $montCre; }
    public function getCodeCli() { return $this->codeCli; }
    public function setCodeCli($codeCli) { $this->codeCli = $codeCli; }
    public function getFilesPath() { return $this->filesPath; }
    public function setFilesPath($filesPath) { $this->filesPath = $filesPath; }
    public function getNumCpt() { return $this->numCpt; }
    public function setNumCpt($numCpt) { $this->numCpt = $numCpt; }
    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; }

    public function save($cnx)
    {
        $sql = "INSERT INTO Credit (date_cre, mont_cre, code_cli, num_cpt, files_path, Status) VALUES (:dateCre, :montCre, :codeCli, :numCpt, :filesPath, :status)";
        $statement = $cnx->prepare($sql);
        $statement->execute([
            ':dateCre' => $this->getDateCre(),
            ':montCre' => $this->montCre,
            ':codeCli' => $this->codeCli,
            ':numCpt' => $this->numCpt,
            ':filesPath' => $this->filesPath,
            ':status' => $this->status
        ]);
        $this->numCre = $cnx->lastInsertId();
    }

    public function update($cnx)
    {
        $sql = "UPDATE Credit SET date_cre=:dateCre, mont_cre=:montCre, code_cli=:codeCli, num_cpt=:numCpt, files_path=:filesPath, Status=:status WHERE num_cre=:numCre";
        $statement = $cnx->prepare($sql);
        $statement->execute([
            ':dateCre' => $this->getDateCre(),
            ':montCre' => $this->montCre,
            ':codeCli' => $this->codeCli,
            ':numCpt' => $this->numCpt,
            ':filesPath' => $this->filesPath,
            ':status' => $this->status,
            ':numCre' => $this->numCre
        ]);
    }

    public function delete($cnx)
    {
        $sql = "DELETE FROM Credit WHERE num_cre=:numCre";
        $statement = $cnx->prepare($sql);
        $statement->execute([':numCre' => $this->numCre]);
    }

    public static function findAll($cnx)
    {
        $sql = "SELECT * FROM Credit";
        $statement = $cnx->prepare($sql);
        $statement->execute();
        $credits = [];
        while ($row = $statement->fetch()) {
            $credits[] = new self(
                $row['num_cre'],
                $row['date_cre'],
                $row['mont_cre'],
                $row['code_cli'],
                $row['num_cpt'],
                $row['files_path'],
                $row['Status']
            );
        }
        return $credits;
    }
    public static function findByNumCre(PDO $cnx, mixed $numCre): ?Credit
    {
        $stmt = $cnx->prepare("SELECT * FROM credit WHERE num_cre = :numCre");
        $stmt->bindParam(':numCre', $numCre, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new self(
                $row['num_cre'],
                $row['date_cre'],
                $row['mont_cre'],
                $row['code_cli'],
                $row['num_cpt'],
                $row['files_path'],
                $row['Status']
            );
        }

        return null;
    }


    public static function updateStatus($cnx, $creditId, $status)
    {
        $sql = "UPDATE Credit SET Status = :status WHERE num_cre = :creditId";
        $statement = $cnx->prepare($sql);
        $statement->execute([
            ':status' => $status,
            ':creditId' => $creditId
        ]);
    }
}
