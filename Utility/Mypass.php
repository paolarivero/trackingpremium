<?php
namespace NvCarga\Bundle\Utility;

class Mypass 
{
    protected $id;

    protected $password;

    public function getId()
    {
        return $this->id;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }	
}
?>
