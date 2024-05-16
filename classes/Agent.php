<?php
class Agent
{
    private $agentId;
    private $username;
    private $password;
    private $role;
    private $cnx;

    public function __construct($cnx, $agentId = 0, $password = '', $username = '', $role = "ROLE_AGENT")
    {
        $this->agentId = $agentId;
        $this->password = $password;
        $this->username = $username;
        $this->role = $role;
        $this->cnx = $cnx;
    }

    public function getAgentId(): mixed
    {
        return $this->agentId;
    }

    public function setAgentId(mixed $agentId): void
    {
        $this->agentId = $agentId;
    }

    public function getUsername(): mixed
    {
        return $this->username;
    }

    public function setUsername(mixed $username): void
    {
        $this->username = $username;
    }

    public function getPassword(): mixed
    {
        return $this->password;
    }

    public function setPassword(mixed $password): void
    {
        $this->password = $password;
    }

    public function getRole(): mixed
    {
        return $this->role;
    }

    public function setRole(mixed $role): void
    {
        $this->role = $role;
    }

    /**
     * @return mixed
     */
    public function getCnx()
    {
        return $this->cnx;
    }

    /**
     * @param mixed $cnx
     */
    public function setCnx($cnx): void
    {
        $this->cnx = $cnx;
    }



    public function ajout()
    {
        $sql = "INSERT INTO agent (username, password_hash, role) VALUES (:username, :password, :isAdmin)";
        $statement = $this->cnx->prepare($sql);
        $statement->execute([
            ':username' => $this->username,
            ':password' => $this->password,
            ':isAdmin' => $this->role,
        ]);
    }

    public function update()
    {
        $sql = "UPDATE agent SET username=:username, password_hash=:password, role=:isAdmin WHERE agentId=:agentId";
        $statement = $this->cnx->prepare($sql);
        $statement->execute([
            ':username' => $this->username,
            ':password' => $this->password,
            ':isAdmin' => $this->role,
            ':agentId' => $this->agentId
        ]);
    }

    public function delete()
    {
        $sql = "DELETE FROM agent WHERE agent_id=:agentId";
        $statement = $this->cnx->prepare($sql);
        $statement->execute([
            ':agentId' => $this->agentId
        ]);
    }

    public static function findAll($cnx)
    {
        $sql = "SELECT * FROM agent";
        $statement = $cnx->prepare($sql);
        $statement->execute();
        $agents = [];

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $agents[] = new self(
                $cnx,
                $row['agent_id'],
                $row['password_hash'],
                $row['username'],
                $row['role']
            );
        }

        return $agents;
    }

    public static function getAgentByUsername($cnx,$username)
    {
        $stmt = $cnx->prepare("SELECT * FROM agent WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new self(
                $cnx,
                $row['agent_id'],
                $row['password_hash'],
                $row['username'],
                $row['role']
            );
        }

        return null;
    }
}
?>
