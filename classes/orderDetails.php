<?php
$filepath = realpath(dirname(__FILE__));
include_once($filepath . '/../lib/database.php');
include_once($filepath . '/../lib/session.php');
include_once($filepath . '/../classes/cart.php');
?>


 
<?php
/**
 * 
 */
class orderDetails
{

    public function getOrderDetails($orderId)
    {
        $query = "SELECT * FROM order_details WHERE orderId = $orderId ";
        $mysqli_result = select($query);
        if ($mysqli_result) {
            $result = mysqli_fetch_all(select($query), MYSQLI_ASSOC);
            return $result;
        }
        return false;
    }
}
?>