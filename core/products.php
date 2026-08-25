<?php
/**
 * Discora - Product Data Access & Filtering Service
 * Connects with MySQL discora_db using secure PDO Prepared Statements
 */

require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/core/functions.php';

/**
 * Fetch filtered, sorted, and paginated products from MySQL
 *
 * @param array $filters (search, platform, category, min_price, max_price, is_new_arrival, is_featured, in_stock)
 * @param string $sort ('featured', 'price_low', 'price_high', 'newest', 'name_asc', 'name_desc')
 * @param int $page Current page (1-indexed)
 * @param int $per_page Products per page
 * @return array ['products' => [...], 'total' => int, 'total_pages' => int, 'current_page' => int]
 */
function get_filtered_products(array $filters = [], string $sort = 'newest', int $page = 1, int $per_page = 12): array {
    $pdo = Database::getConnection();

    $where = ["pr.status = 'Active'"];
    $params = [];

    // 1. Search Query (Search title, description, publisher, platform name, genre)
    if (!empty($filters['search'])) {
        $search = trim($filters['search']);
        $where[] = "(pr.product_name LIKE :search_title 
                    OR pr.description LIKE :search_desc 
                    OR pr.genre LIKE :search_genre 
                    OR pr.publisher LIKE :search_pub 
                    OR pl.platform_name LIKE :search_plat)";
        $searchParam = '%' . $search . '%';
        $params[':search_title'] = $searchParam;
        $params[':search_desc']  = $searchParam;
        $params[':search_genre'] = $searchParam;
        $params[':search_pub']   = $searchParam;
        $params[':search_plat']  = $searchParam;
    }

    // 2. Platform Filter (Supports single slug/id or array of slugs)
    if (!empty($filters['platform'])) {
        $platforms = (array)$filters['platform'];
        $platPlaceholders = [];
        foreach ($platforms as $idx => $plat) {
            $keySlug = ":plat_slug_" . $idx;
            $keyName = ":plat_name_" . $idx;
            $keyId   = ":plat_id_" . $idx;
            $platPlaceholders[] = "(pl.slug = $keySlug OR pl.platform_name = $keyName OR pr.platform_id = $keyId)";
            $params[$keySlug] = $plat;
            $params[$keyName] = $plat;
            $params[$keyId]   = is_numeric($plat) ? (int)$plat : 0;
        }
        $where[] = '(' . implode(' OR ', $platPlaceholders) . ')';
    }

    // 3. Category Filter (Supports single slug/id or array of slugs)
    if (!empty($filters['category'])) {
        $categories = (array)$filters['category'];
        $catPlaceholders = [];
        foreach ($categories as $idx => $cat) {
            $keySlug = ":cat_slug_" . $idx;
            $keyName = ":cat_name_" . $idx;
            $keyId   = ":cat_id_" . $idx;
            $keyPid  = ":cat_pid_" . $idx;
            $catPlaceholders[] = "(c.slug = $keySlug OR c.category_name = $keyName OR pr.category_id = $keyId OR c.parent_id = $keyPid)";
            $params[$keySlug] = $cat;
            $params[$keyName] = $cat;
            $params[$keyId]   = is_numeric($cat) ? (int)$cat : 0;
            $params[$keyPid]  = is_numeric($cat) ? (int)$cat : 0;
        }
        $where[] = '(' . implode(' OR ', $catPlaceholders) . ')';
    }

    // 4. Genre Filter
    if (!empty($filters['genre'])) {
        $genres = (array)$filters['genre'];
        $genrePlaceholders = [];
        foreach ($genres as $idx => $g) {
            $key = ":genre_" . $idx;
            $genrePlaceholders[] = "pr.genre LIKE $key";
            $params[$key] = '%' . trim($g) . '%';
        }
        $where[] = '(' . implode(' OR ', $genrePlaceholders) . ')';
    }

    // 5. Price Range
    if (isset($filters['min_price']) && is_numeric($filters['min_price']) && $filters['min_price'] >= 0) {
        $where[] = "COALESCE(pr.discount_price, pr.price) >= :min_price";
        $params[':min_price'] = (float)$filters['min_price'];
    }
    if (isset($filters['max_price']) && is_numeric($filters['max_price']) && $filters['max_price'] > 0) {
        $where[] = "COALESCE(pr.discount_price, pr.price) <= :max_price";
        $params[':max_price'] = (float)$filters['max_price'];
    }

    // 6. New Arrivals
    if (!empty($filters['is_new_arrival'])) {
        $where[] = "pr.is_new_arrival = 1";
    }

    // 7. Featured Products
    if (!empty($filters['is_featured'])) {
        $where[] = "pr.is_featured = 1";
    }

    // 8. In Stock Only
    if (!empty($filters['in_stock'])) {
        $where[] = "pr.stock_quantity > 0";
    }

    $whereSql = implode(' AND ', $where);

    // Whitelisted Sorting Options (Security against SQL Injection)
    $sortOptions = [
        'featured'   => 'pr.is_featured DESC, pr.product_id DESC',
        'price_low'  => 'COALESCE(pr.discount_price, pr.price) ASC',
        'price_high' => 'COALESCE(pr.discount_price, pr.price) DESC',
        'newest'     => 'pr.product_id DESC',
        'name_asc'   => 'pr.product_name ASC',
        'name_desc'  => 'pr.product_name DESC',
    ];
    $orderBy = $sortOptions[$sort] ?? $sortOptions['newest'];

    // 1. Total Count Query
    $countSql = "
        SELECT COUNT(DISTINCT pr.product_id) as total
        FROM products pr
        JOIN platforms pl ON pr.platform_id = pl.platform_id
        LEFT JOIN categories c ON pr.category_id = c.category_id
        WHERE $whereSql
    ";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    // 2. Pagination Math
    $page = max(1, $page);
    $per_page = max(1, min(48, $per_page));
    $total_pages = $total > 0 ? (int)ceil($total / $per_page) : 1;
    if ($page > $total_pages) $page = $total_pages;
    $offset = ($page - 1) * $per_page;

    // 3. Products List Query
    $productsSql = "
        SELECT 
            pr.product_id,
            pr.category_id,
            pr.platform_id,
            pr.product_name,
            pr.slug,
            pr.edition,
            pr.genre,
            pr.description,
            pr.price,
            pr.discount_price,
            pr.stock_quantity,
            pr.product_type,
            pr.release_date,
            pr.publisher,
            pr.is_new_arrival,
            pr.is_featured,
            pr.status,
            pl.platform_name,
            pl.slug as platform_slug,
            pl.brand as platform_brand,
            c.category_name,
            c.slug as category_slug,
            COALESCE(
                (SELECT img.image_path FROM product_images img WHERE img.product_id = pr.product_id ORDER BY img.is_primary DESC, img.display_order ASC LIMIT 1),
                'assets/images/products/placeholder.jpg'
            ) as primary_image,
            COALESCE(
                (SELECT ROUND(AVG(r.rating), 1) FROM reviews r WHERE r.product_id = pr.product_id AND r.review_status = 'Approved'),
                5.0
            ) as avg_rating,
            COALESCE(
                (SELECT COUNT(r.review_id) FROM reviews r WHERE r.product_id = pr.product_id AND r.review_status = 'Approved'),
                0
            ) as review_count
        FROM products pr
        JOIN platforms pl ON pr.platform_id = pl.platform_id
        LEFT JOIN categories c ON pr.category_id = c.category_id
        WHERE $whereSql
        ORDER BY $orderBy
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $pdo->prepare($productsSql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $products = $stmt->fetchAll();

    return [
        'products'     => $products,
        'total'        => $total,
        'total_pages'  => $total_pages,
        'current_page' => $page,
        'per_page'     => $per_page
    ];
}

/**
 * Retrieve single product details by ID or Slug with all associated images & reviews
 */
function get_product_details(int|string $identifier): ?array {
    $pdo = Database::getConnection();

    $where = is_numeric($identifier) ? "pr.product_id = :id" : "pr.slug = :id";
    
    $sql = "
        SELECT 
            pr.product_id,
            pr.category_id,
            pr.platform_id,
            pr.product_name,
            pr.slug,
            pr.edition,
            pr.genre,
            pr.description,
            pr.price,
            pr.discount_price,
            pr.stock_quantity,
            pr.product_type,
            pr.release_date,
            pr.publisher,
            pr.is_new_arrival,
            pr.is_featured,
            pr.status,
            pl.platform_name,
            pl.slug as platform_slug,
            pl.brand as platform_brand,
            c.category_name,
            c.slug as category_slug
        FROM products pr
        JOIN platforms pl ON pr.platform_id = pl.platform_id
        LEFT JOIN categories c ON pr.category_id = c.category_id
        WHERE $where AND pr.status = 'Active'
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $identifier]);
    $product = $stmt->fetch();

    if (!$product) {
        return null;
    }

    // Fetch all images for this product
    $imgStmt = $pdo->prepare("
        SELECT image_id, image_path, alt_text, is_primary, display_order
        FROM product_images
        WHERE product_id = :pid
        ORDER BY is_primary DESC, display_order ASC, image_id ASC
    ");
    $imgStmt->execute([':pid' => $product['product_id']]);
    $images = $imgStmt->fetchAll();

    if (empty($images)) {
        $images = [[
            'image_id'      => 0,
            'image_path'    => 'assets/images/products/placeholder.jpg',
            'alt_text'      => $product['product_name'],
            'is_primary'    => 1,
            'display_order' => 1
        ]];
    }

    $product['images'] = $images;
    $product['primary_image'] = $images[0]['image_path'];

    // Fetch approved customer reviews
    $revStmt = $pdo->prepare("
        SELECT r.review_id, r.user_id, r.rating, r.review_text, r.created_at, u.full_name as author_name, u.avatar as author_avatar
        FROM reviews r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.product_id = :pid AND r.review_status = 'Approved'
        ORDER BY r.created_at DESC
    ");
    $revStmt->execute([':pid' => $product['product_id']]);
    $reviews = $revStmt->fetchAll();

    $product['reviews'] = $reviews;
    $product['review_count'] = count($reviews);

    if ($product['review_count'] > 0) {
        $ratingsSum = array_sum(array_column($reviews, 'rating'));
        $product['avg_rating'] = round($ratingsSum / $product['review_count'], 1);
    } else {
        $product['avg_rating'] = 5.0; // Default clean rating
    }

    return $product;
}

/**
 * Fetch related products in the same platform or category
 */
function get_related_products(int $productId, ?int $categoryId, int $platformId, int $limit = 4): array {
    $pdo = Database::getConnection();

    $sql = "
        SELECT 
            pr.product_id,
            pr.product_name,
            pr.slug,
            pr.genre,
            pr.price,
            pr.discount_price,
            pr.stock_quantity,
            pr.is_new_arrival,
            pr.is_featured,
            pl.platform_name,
            pl.slug as platform_slug,
            COALESCE(
                (SELECT img.image_path FROM product_images img WHERE img.product_id = pr.product_id ORDER BY img.is_primary DESC, img.display_order ASC LIMIT 1),
                'assets/images/products/placeholder.jpg'
            ) as primary_image
        FROM products pr
        JOIN platforms pl ON pr.platform_id = pl.platform_id
        WHERE pr.product_id != :pid 
          AND (pr.platform_id = :plat OR pr.category_id = :cat)
          AND pr.status = 'Active'
        ORDER BY RAND()
        LIMIT :lim
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':pid', $productId, PDO::PARAM_INT);
    $stmt->bindValue(':plat', $platformId, PDO::PARAM_INT);
    $stmt->bindValue(':cat', $categoryId ?? 0, PDO::PARAM_INT);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Get list of all active categories with product counts
 */
function get_categories_list(): array {
    $pdo = Database::getConnection();
    $sql = "
        SELECT c.category_id, c.category_name, c.slug, c.parent_id, COUNT(pr.product_id) as product_count
        FROM categories c
        LEFT JOIN products pr ON c.category_id = pr.category_id AND pr.status = 'Active'
        WHERE c.status = 'Active'
        GROUP BY c.category_id
        ORDER BY c.display_order ASC, c.category_name ASC
    ";
    return $pdo->query($sql)->fetchAll();
}

/**
 * Get list of all platforms with product counts
 */
function get_platforms_list(): array {
    $pdo = Database::getConnection();
    $sql = "
        SELECT pl.platform_id, pl.platform_name, pl.slug, pl.brand, COUNT(pr.product_id) as product_count
        FROM platforms pl
        LEFT JOIN products pr ON pl.platform_id = pr.platform_id AND pr.status = 'Active'
        GROUP BY pl.platform_id
        ORDER BY pl.display_order ASC
    ";
    return $pdo->query($sql)->fetchAll();
}
