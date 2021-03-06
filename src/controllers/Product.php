<?php
require_once __DIR__ . '/User.php';

/*
 * adds product to database and displays them
 */

class Product {

    /* 
     * Creates a new Product
     * @param name: name of the product
     * @param price: price of the product
     * @param quantity: number currently in stock
     * @param image: visual for product, a path in the directory
     * @param description: describes the product
     * @param catagory: the categories that the product fits into, csv
     * @param token: A User authentication token
     */
    static function Create($args): void {
        //checks if the required variables were given
        if(!($args['name'] && $args['price'] && $args['quantity'] && $args['image'] && $args['description'] && $args['catagory'] && $args['token'])) {
            error("Missing required fields");
            return;
        }

        //check if user has access
        try {
            $user = new SiteUser(null, $args['token']);
            if(!$user->isAuth() || $user->type != "admin") {
                error("User doesn't have privileges to add items");
                return;
            }
        }
        catch(Exception $e) {
            error($e->getMessage());
            return;
        }


        //insert into database
        $db = $GLOBALS['database'];
        if(!$db->query("INSERT INTO product (name, price, quantity, image, description, catagory) VALUES('"
            . $args['name'] . "', '"
            . $args['price'] . "', '"
            . $args['quantity'] . "', '"
            . $args['image'] . "', '"
            . $args['description'] . "', '"
            . $args['catagory'] . "');")){
            error($db->error);
            return;
        }
        success();
    }
    /* 
     * Gets info on an existing Product
     * @param id: id of the product to query from db
     * @return The product name, price, quantity, image, and description
     */
    static function Get($args): void {
        if(!$args['id']) {
            error("Product id required");
            return;
        }
        try {
            $item = new ViewableProduct($args['id']);
            $output = new HTTPResponse();
            $payload->name = $item->name;
            $payload->price = $item->price;
            $payload->quantity = $item->quantity;
            $payload->image = $item->image;
            $payload->description = $item->description;
            $output->setPayload($payload);
            $output->complete();
        }
        catch (Exception $e) {
            error($e->getMessage());
        }
    }

    /*
     * Gets info about a group of products
     * @param catagory: The catagory of the product.
     * @param search: A search string
     * @return A list of products
     */
    static function GetAll($args): void {
        $sql = "SELECT * FROM product";
        if($args['catagory'] && !$args['search']) {
            //select all from given catagory
            $sql .= " WHERE catagory LIKE '%" . $args['catagory'] . "%';";
        }
        else if($args['search'] && !$args['catagory']) {
            //select all that meet search parameter
            $sql .= " WHERE name LIKE '%" . $args['search'] 
                . "%' OR description LIKE '%" . $args['search'] . "%';";
        }
        else if($args['search'] && $args['catagory']) {
            //select all from catagory that match search
            $sql .= " WHERE catagory LIKE '%" . $args['catagory'] 
                . " AND (name LIKE '%" . $args['search'] 
                . "%' OR description LIKE '%" . $args['search'] . "%');";
        }
        else {
            //return all items
            $sql .= ";";
        }

        $db = $GLOBALS['database'];
        $products = $db->query($sql);
        $output = new HTTPResponse();
        $payload->products = array();
        while($product = $products->fetch_assoc()) {
            //add each product to the payload
            $item = new stdClass();
            $item->id = $product['id'];
            $item->name = $product['name'];
            $item->price = $product['price'];
            $item->image = $product['image'];
            $payload->products[] = $item;
        }
        $output->setPayload($payload);
        $output->complete();
    }
     
	/* 
     * Deletes an existing Product
     */
    static function delete(): void {
        echo "<h1>unimplimented, see functional req.s </h1>";
    }
     /* 
     * edits an existing Product
     */
    static function edit(): void {
        echo "<h1>unimplimented, see functional req.s </h1>";
    }


}
class ViewableProduct{
    private $id;
    public $name;
    public $price;
    public $quantity;
    public $image;
    public $description;
    
    function __construct($id) {
        $db = $GLOBALS['database'];
	$q = "SELECT * FROM product WHERE id = '" . $id . "';";
	echo $q;
        $result = $db->query("SELECT * FROM product WHERE id = '" . $id . "';"); //fetch product by id from the db
        if(mysqli_num_rows($result) < 1) { //if product with this name gave a NO result
            throw new Exception("Product does not exist"); //dne, return error
        }
        else{//there was a result
            //load all rows from query into this object
            $row = mysqli_fetch_assoc($result);
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->price = $row['price'];
            $this->quantity = $row['quantity'];
            $this->image = $row['image'];
            $this->description = $row['description'];
        }
    }
}
?>
