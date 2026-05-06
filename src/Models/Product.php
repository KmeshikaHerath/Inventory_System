<?
namespace App\Models;
use App\Core\Database;

use PDO;
/** 
class Product
{
    public static function create($data)
    {
        $db = Database::connect();

        $stmt = $db->prepare("
            INSERT INTO products (name, price, quantity)
            VALUES (:name, :price, :quantity)
        ");

        return $stmt->execute([
            'name' => $data['name'],
            'price' => $data['price'],
            'quantity' => $data['quantity']
        ]);
    }
}
*/