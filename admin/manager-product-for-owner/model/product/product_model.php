<?php
include_once __DIR__ . '/../../lib/database.php';

class Product_Model
{
    private $database;

    public function __construct()
    {
        $this->database = new Database();
    }

    public function get_product_by_id($id)
    {
        $query = "SELECT * FROM `product` WHERE id = $id;";
        $result = $this->database->select($query);
        return $result->fetch_all(MYSQLI_ASSOC)[0];
    }

    public function get_product_list($category_id)
    {
        $query = "SELECT * FROM `product` WHERE category_id = '$category_id';";
        $result = $this->database->select($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function sort_product_list($sort)
    {
        if ($sort == '1') {
            return " ORDER BY price ASC";
        } elseif ($sort == "2") {
            return " ORDER BY price DESC";
        } elseif ($sort == "3") {
            return " ORDER BY product_name ASC";
        } elseif ($sort == "4") {
            return " ORDER BY product_name DESC";
        } else {
            return "";
        }
    }

    public function filter_product_list($color, $size, $price, $category_id, $sort)
    {
        if (empty($color) && empty($size) && empty($price)) {

            $query = "SELECT DISTINCT product.id,product.product_name,product.price,product.avatar
            FROM `product` WHERE category_id = $category_id";
        } elseif (empty($color) && empty($size)) {

            $query = "SELECT DISTINCT product.id,product.product_name,product.price,product.avatar
            FROM product
            WHERE category_id = $category_id
            AND product.price < $price";
        } elseif (empty($size) && empty($price)) {

            if (substr($color, -1) == ',') {
                $color = substr($color, 0, -1);
            }
            $query = "SELECT DISTINCT product.id,product.product_name,product.price,product.avatar
            FROM product, product_has_colors
            WHERE category_id = $category_id
            AND product.id = product_has_colors.product_id
            AND product_has_colors.color_id in ($color)";
        } elseif (empty($color) && empty($price)) {

            if (substr($size, -1) == ',') {
                $size = substr($size, 0, -1);
            }

            $query = "SELECT DISTINCT product.id,product.product_name,product.price,product.avatar
            FROM product, color_has_sizes
            WHERE category_id = $category_id
            AND product.id = color_has_sizes.product_id
            AND color_has_sizes.size_id in ($size)";
        } elseif (empty($color)) {

            if (substr($size, -1) == ',') {
                $size = substr($size, 0, -1);
            }

            $query = "SELECT DISTINCT product.id,product.product_name,product.price,product.avatar
            FROM product, color_has_sizes
            WHERE category_id = $category_id
            AND product.id = color_has_sizes.product_id
            AND product.price < $price
            AND color_has_sizes.size_id in ($size)";
        } elseif (empty($size)) {

            if (substr($color, -1) == ',') {
                $color = substr($color, 0, -1);
            }

            $query = "SELECT DISTINCT product.id,product.product_name,product.price,product.avatar
            FROM product, product_has_colors
            WHERE category_id = $category_id
            AND product.id = product_has_colors.product_id
            AND product.price < $price
            AND product_has_colors.color_id in ($color)";
        } elseif (empty($price)) {

            if (substr($color, -1) == ',') {
                $color = substr($color, 0, -1);
            }
            if (substr($size, -1) == ',') {
                $size = substr($size, 0, -1);
            }

            $query = "SELECT DISTINCT product.id,product.product_name,product.price,product.avatar
            FROM product, color_has_sizes
            WHERE category_id = $category_id
            AND product.id = color_has_sizes.product_id
            AND color_has_sizes.color_id in ($color)
            AND color_has_sizes.size_id in ($size)";
        } else {

            if (substr($color, -1) == ',') {
                $color = substr($color, 0, -1);
            }
            if (substr($size, -1) == ',') {
                $size = substr($size, 0, -1);
            }

            $query = "SELECT DISTINCT product.id,product.product_name,product.price,product.avatar
            FROM product, color_has_sizes
            WHERE category_id = $category_id
            AND product.id = color_has_sizes.product_id
            AND product.price < $price
            AND color_has_sizes.color_id in ($color)
            AND color_has_sizes.size_id in ($size)";
        }

        $query .= $this->sort_product_list($sort);

        $result = $this->database->select($query);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return false;
        }
    }

    public function search_product_list($search)
    {
        $query = "SELECT * FROM `product` WHERE product_name like '%$search%';";
        $result = $this->database->select($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function get_colors_of_product($product_id)
    {
        $query = "SELECT color_id, product_img, color_name FROM `product_has_colors`, `color` WHERE id=color_id and product_id = $product_id;";
        $result = $this->database->select($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function get_sizes_of_productColor($product_id, $color_id)
    {
        $query = "SELECT size_id, size_name, quantity FROM `color_has_sizes`, `size` WHERE size_id=id and color_id=$color_id and product_id = $product_id;";
        $result = $this->database->select($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function get_quantity($product_id, $color_id, $size_id)
    {
        $query = "SELECT quantity FROM `color_has_sizes` WHERE size_id=$size_id and color_id=$color_id and product_id = $product_id;";
        $result = $this->database->select($query);
        return $result->fetch_all(MYSQLI_ASSOC)[0]['quantity'];
    }

    public function get_avatar($product_id, $color_id)
    {
        $query = "SELECT product_img FROM `product_has_colors` WHERE color_id=$color_id and product_id = $product_id;";
        $result = $this->database->select($query);
        return $result->fetch_all(MYSQLI_ASSOC)[0]['product_img'];
    }

    public function add_product_infor($productname, $productprice, $productdesc, $category_id, $colorid, $productimage, $productsize)
    {
        // validate input
        $productname = $this->database->validateInput($productname);
        $productdesc = $this->database->validateInput($productdesc);
        $productimage = $this->database->validateInput($productimage);

        $query = "INSERT INTO `product`(`id`, `product_name`, `price`, `description`, `category_id`) VALUES (NULL, '$productname', '$productprice', '$productdesc', '$category_id')";
        $product_id = $this->database->insert_for_autoIncrement($query);

        echo "<h1>" . var_dump($product_id) . "</h1>";


        

        $query = "INSERT INTO `product_has_colors` (`product_id`, `color_id`, `product_img`) VALUES ('$product_id', '$colorid', '$productimage')";
        $result1 = $this->database->insert($query);

        echo "<h1>" . var_dump($product_id) . "</h1>";

        for ($i = 24; $i <= 45; $i++) {
            if ($productsize[$i - 24] == true) {

                echo "<h1>" . $i . "</h1>";

                $query_sizeid = "SELECT * FROM `size` WHERE `size_name` = '$i'";
                $result_sizeid = $this->database->select($query_sizeid);

                echo "<h1>" . var_dump($result_sizeid) . "</h1>";

                $size_id = $result_sizeid->fetch_all(MYSQLI_ASSOC)[0]['id'];

                $query = "INSERT INTO `color_has_sizes` (`product_id`, `color_id`, `size_id`, `quantity`) VALUES ('$product_id', '$colorid', '$size_id', '0')";

                $result2 = $this->database->insert($query);
                
                if ($result2 == true){
                    echo "<pre> insert size '$i' thanh cong </pre>";
                    
                }
            }
        }

        echo "<h1>" . var_dump($product_id) . "</h1>";
    }

    // public function add_product_infor($productname, $productprice, $productdesc, $category_id){
    //     echo '<h1> eerrr1 </h1>';
    //     $productname = $this->database->validateInput($productname);
    //     $productdesc = $this->database->validateInput($productdesc);
    //     $query = "INSERT INTO `product`(`product_name`, `price`, `description`, `category_id`) VALUES ('$productname', '$productprice', '$productdesc', '$category_id')";
    //     $result = $this->database->insert($query);
    //     return ($result) ? $result->fetch_all(MYSQLI_ASSOC) : false;
    // }
}
