<?php
class Repuesto
{
    private $conn;
    private $table_name = "repuesto";

    public $id;
    public $nombre;
    public $descripcion;
    public $stock_actual;
    public $stock_minimo;

    public function __construct($db)
    {
        $this->conn = $db;
    }

   
    public function leer()
    {
        $query = "SELECT
                    id, nombre, descripcion, stock_actual, stock_minimo
                FROM
                    " . $this->table_name . "
                ORDER BY
                    nombre ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }


    public function obtenerPorId($id)
    {
        $query = "SELECT
                    id, nombre, descripcion, stock_actual, stock_minimo
                FROM
                    " . $this->table_name . "
                WHERE
                    id = ?
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->nombre = $row['nombre'];
            $this->descripcion = $row['descripcion'];
            $this->stock_actual = $row['stock_actual'];
            $this->stock_minimo = $row['stock_minimo'];
            return $row;
        } else {
            return null;
        }
    }

    public function crear()
    {
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    nombre=:nombre, descripcion=:descripcion, stock_actual=:stock_actual, stock_minimo=:stock_minimo";

        $stmt = $this->conn->prepare($query);

        
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->stock_actual = htmlspecialchars(strip_tags($this->stock_actual));
        $this->stock_minimo = htmlspecialchars(strip_tags($this->stock_minimo));

       
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":stock_actual", $this->stock_actual);
        $stmt->bindParam(":stock_minimo", $this->stock_minimo);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    
    public function actualizar()
    {
        $query = "UPDATE
                    " . $this->table_name . "
                SET
                    nombre=:nombre,
                    descripcion=:descripcion,
                    stock_actual=:stock_actual,
                    stock_minimo=:stock_minimo
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);

      
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->stock_actual = htmlspecialchars(strip_tags($this->stock_actual));
        $this->stock_minimo = htmlspecialchars(strip_tags($this->stock_minimo));

      
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":stock_actual", $this->stock_actual);
        $stmt->bindParam(":stock_minimo", $this->stock_minimo);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

   
    public function eliminar()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}