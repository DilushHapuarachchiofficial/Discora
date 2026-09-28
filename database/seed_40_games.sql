-- ==========================================================
-- Discora 40 Games Seed Update
-- 10 PS4, 10 PS5, 10 Xbox One, 10 Xbox Series X|S + Hardware
-- ==========================================================

USE `discora_db`;

SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;

-- Clean existing products and images to refresh with 40 full games
TRUNCATE TABLE `product_images`;
TRUNCATE TABLE `products`;

-- -------------------------------------------------------------
-- 1. INSERT 40 AUTHENTIC PHYSICAL DISC GAMES + HARDWARE
-- -------------------------------------------------------------

-- PS4 Games (Products 1-10) [platform_id: 2, category_id: 7]
INSERT INTO `products` (`product_id`, `category_id`, `platform_id`, `product_name`, `slug`, `edition`, `genre`, `description`, `price`, `discount_price`, `stock_quantity`, `product_type`, `release_date`, `publisher`, `is_new_arrival`, `is_featured`, `status`) VALUES
(1, 7, 2, 'A Plague Tale: Innocence', 'a-plague-tale-innocence-ps4', 'Standard Physical Edition', 'Action / Adventure', 'Follow the grim tale of young Amicia and her little brother Hugo, in a heartbreaking journey through the darkest hours of history.', 39.99, 49.99, 25, 'Physical Disc', '2019-05-14', 'Focus Home Interactive', 0, 1, 'Active'),
(2, 7, 2, 'Ghost of Tsushima', 'ghost-of-tsushima-ps4', 'Standard Physical Edition', 'Open World / Action', 'In the late 13th century, Jin Sakai must forge a new path, the path of the Ghost, to wage an unconventional war for the freedom of Tsushima.', 39.99, 59.99, 30, 'Physical Disc', '2020-07-17', 'Sony Interactive Entertainment', 0, 1, 'Active'),
(3, 7, 2, 'The Last of Us Remastered', 'the-last-of-us-remastered-ps4', 'PlayStation Hits Edition', 'Action / Survival Horror', 'Winner of over 200 Game of the Year awards. Explore a brutal post-pandemic world with Joel and Ellie across a devastated America.', 19.99, 29.99, 40, 'Physical Disc', '2014-07-29', 'Sony Interactive Entertainment', 0, 1, 'Active'),
(4, 7, 2, 'Bloodborne', 'bloodborne-ps4', 'PlayStation Hits Edition', 'Action / RPG', 'Face your fears as you search for answers in the ancient city of Yharnam, now cursed with a strange endemic illness spreading through the streets.', 19.99, 29.99, 22, 'Physical Disc', '2015-03-24', 'Sony Interactive Entertainment', 0, 0, 'Active'),
(5, 7, 2, 'God of War (2018)', 'god-of-war-2018-ps4', 'Standard Physical Edition', 'Action / Adventure', 'Living as a man outside the shadow of the gods, Kratos must adapt to unfamiliar Norse lands, unexpected threats, and a second chance at being a father.', 29.99, 39.99, 35, 'Physical Disc', '2018-04-20', 'Sony Interactive Entertainment', 0, 1, 'Active'),
(6, 7, 2, 'Uncharted 4: A Thief\'s End', 'uncharted-4-a-thiefs-end-ps4', 'PlayStation Hits Edition', 'Action / Adventure', 'Several years after his last adventure, retired fortune hunter Nathan Drake is forced back into the dangerous world of thieves.', 19.99, 29.99, 28, 'Physical Disc', '2016-05-10', 'Sony Interactive Entertainment', 0, 0, 'Active'),
(7, 7, 2, 'Horizon Zero Dawn: Complete Edition', 'horizon-zero-dawn-ps4', 'Complete Physical Edition', 'Action / RPG', 'Experience Aloy\'s entire legendary quest to unravel the mysteries of a world ruled by deadly Machines on a lush, post-apocalyptic Earth.', 24.99, 34.99, 30, 'Physical Disc', '2017-12-05', 'Sony Interactive Entertainment', 0, 1, 'Active'),
(8, 7, 2, 'Marvel\'s Spider-Man', 'marvels-spider-man-ps4', 'Game of the Year Edition', 'Action / Adventure', 'Starring one of the world\'s most iconic Super Heroes, Marvel\'s Spider-Man features the acrobatic abilities, improvisation and web-slinging.', 29.99, 39.99, 32, 'Physical Disc', '2018-09-07', 'Sony Interactive Entertainment', 0, 0, 'Active'),
(9, 7, 2, 'Red Dead Redemption 2', 'red-dead-redemption-2-ps4', 'Standard 2-Disc Edition', 'Action / Open World', 'America, 1899. Arthur Morgan and the Van der Linde gang are outlaws on the run across the vast and rugged heartland of America.', 39.99, 59.99, 45, 'Physical Disc', '2018-10-26', 'Rockstar Games', 0, 1, 'Active'),
(10, 7, 2, 'The Witcher 3: Wild Hunt', 'the-witcher-3-wild-hunt-ps4', 'Complete Edition', 'RPG / Open World', 'You are Geralt of Rivia, mercenary monster slayer. Before you stands a war-torn, monster-infested continent you can explore at will.', 29.99, 49.99, 38, 'Physical Disc', '2015-05-19', 'CD Projekt RED', 0, 1, 'Active'),

-- PS5 Games (Products 11-20) [platform_id: 1, category_id: 6]
(11, 6, 1, 'Marvel\'s Spider-Man 2', 'marvels-spider-man-2-ps5', 'Launch Physical Edition', 'Action / Adventure', 'Spider-Men Peter Parker and Miles Morales face the ultimate test of strength inside and outside the mask as they fight to save the city from Venom.', 69.99, NULL, 50, 'Physical Disc', '2023-10-20', 'Sony Interactive Entertainment', 1, 1, 'Active'),
(12, 6, 1, 'God of War Ragnarök', 'god-of-war-ragnarok-ps5', 'Standard Physical Edition', 'Action / Adventure', 'Join Kratos and Atreus on a mythic journey for answers before Ragnarök arrives. Together, father and son must put everything on the line.', 59.99, 69.99, 42, 'Physical Disc', '2022-11-09', 'Sony Interactive Entertainment', 0, 1, 'Active'),
(13, 6, 1, 'Assassin\'s Creed Mirage', 'assassins-creed-mirage-ps5', 'Launch Physical Edition', 'Action / Stealth', 'Experience the story of Basim, a cunning street thief with nightmarish visions, seeking answers and justice in 9th-century Baghdad.', 49.99, NULL, 38, 'Physical Disc', '2023-10-05', 'Ubisoft', 1, 1, 'Active'),
(14, 6, 1, 'Demon\'s Souls', 'demons-souls-ps5', 'Standard Physical Edition', 'Action / RPG', 'Entirely rebuilt from the ground up, this remake invites you to experience the unsettling story and ruthless combat of Demon\'s Souls.', 49.99, 69.99, 20, 'Physical Disc', '2020-11-12', 'Sony Interactive Entertainment', 0, 0, 'Active'),
(15, 6, 1, 'Final Fantasy XVI', 'final-fantasy-xvi-ps5', 'Standard Physical Edition', 'Action / RPG', 'An epic dark fantasy world where the fate of the land is decided by the mighty Eikons and the Dominants who wield them.', 59.99, 69.99, 33, 'Physical Disc', '2023-06-22', 'Square Enix', 1, 1, 'Active'),
(16, 6, 1, 'Horizon Forbidden West', 'horizon-forbidden-west-ps5', 'Standard Physical Edition', 'Action / RPG', 'Join Aloy as she braves the Forbidden West – a majestic but dangerous frontier that conceals mysterious new threats.', 49.99, 69.99, 28, 'Physical Disc', '2022-02-18', 'Sony Interactive Entertainment', 0, 1, 'Active'),
(17, 6, 1, 'Ratchet & Clank: Rift Apart', 'ratchet-and-clank-rift-apart-ps5', 'Standard Physical Edition', 'Action / Platformer', 'Blast your way through an interdimensional adventure with Ratchet and Clank as they take on an evil emperor from another reality.', 49.99, 69.99, 25, 'Physical Disc', '2021-06-11', 'Sony Interactive Entertainment', 0, 0, 'Active'),
(18, 6, 1, 'Returnal', 'returnal-ps5', 'Standard Physical Edition', 'Roguelike / Sci-Fi Shooter', 'After crash-landing on a shape-shifting alien world, Selene must search through the barren landscape of an ancient civilization for her escape.', 44.99, 69.99, 18, 'Physical Disc', '2021-04-30', 'Sony Interactive Entertainment', 0, 0, 'Active'),
(19, 6, 1, 'Resident Evil 4 Remake', 'resident-evil-4-ps5', 'Standard Physical Edition', 'Survival Horror', 'Survival is only the beginning. Six years have passed since the biological disaster in Raccoon City. Leon S. Kennedy tracks the president\'s missing daughter.', 59.99, NULL, 35, 'Physical Disc', '2023-03-24', 'Capcom', 1, 1, 'Active'),
(20, 6, 1, 'Elden Ring', 'elden-ring-ps5', 'Standard Physical Edition', 'Action / RPG', 'Rise, Tarnished, and be guided by grace to brandish the power of the Elden Ring and become an Elden Lord in the Lands Between.', 59.99, NULL, 48, 'Physical Disc', '2022-02-25', 'Bandai Namco Entertainment', 1, 1, 'Active'),

-- Xbox One Games (Products 21-30) [platform_id: 4, category_id: 9]
(21, 9, 4, 'Gears 5', 'gears-5-xbox-one', 'Standard Physical Edition', 'Third-Person Shooter', 'With all-out war descending, Kait Diaz breaks away to uncover her connection to the enemy and discovers the true danger to Sera – herself.', 29.99, 39.99, 20, 'Physical Disc', '2019-09-10', 'Xbox Game Studios', 0, 1, 'Active'),
(22, 9, 4, 'Forza Horizon 4', 'forza-horizon-4-xbox-one', 'Standard Physical Edition', 'Open World Racing', 'Dynamic seasons change everything at the world\'s greatest automotive festival. Go it alone or team up with others to explore beautiful Britain.', 34.99, 49.99, 25, 'Physical Disc', '2018-10-02', 'Xbox Game Studios', 0, 1, 'Active'),
(23, 9, 4, 'Halo 5: Guardians', 'halo-5-guardians-xbox-one', 'Standard Physical Edition', 'First-Person Shooter', 'A mysterious and unstoppable force threatens the galaxy, the Master Chief is missing and his loyalty questioned.', 19.99, 29.99, 18, 'Physical Disc', '2015-10-27', 'Xbox Game Studios', 0, 0, 'Active'),
(24, 9, 4, 'Quantum Break', 'quantum-break-xbox-one', 'Standard Physical Edition', 'Action / Sci-Fi', 'When time breaks, catastrophe becomes your playground. As Jack Joyce, you\'ll fight your way through epic disasters that stutter back and forth in time.', 19.99, 29.99, 15, 'Physical Disc', '2016-04-05', 'Xbox Game Studios', 0, 0, 'Active'),
(25, 9, 4, 'Sunset Overdrive', 'sunset-overdrive-xbox-one', 'Standard Physical Edition', 'Action / Comedy Shooter', 'Don\'t Drink the Fizz. Sunset Overdrive transforms an open-world apocalypse into your tactical playground with hyper-agility and crazy weapons.', 14.99, 24.99, 12, 'Physical Disc', '2014-10-28', 'Xbox Game Studios', 0, 0, 'Active'),
(26, 9, 4, 'Sea of Thieves', 'sea-of-thieves-xbox-one', 'Anniversary Physical Edition', 'Action / Multiplayer', 'Sea of Thieves offers the essential pirate experience, from sailing and fighting to exploring and looting – everything you need to live the pirate life.', 29.99, 39.99, 22, 'Physical Disc', '2018-03-20', 'Xbox Game Studios', 0, 1, 'Active'),
(27, 9, 4, 'Ori and the Will of the Wisps', 'ori-and-the-will-of-the-wisps-xbox-one', 'Standard Physical Edition', 'Platformer / Adventure', 'Embark on an all-new adventure in a vast, exotic world where you\'ll encounter towering enemies and challenging puzzles on your quest.', 24.99, 29.99, 19, 'Physical Disc', '2020-03-11', 'Xbox Game Studios', 0, 1, 'Active'),
(28, 9, 4, 'Rise of the Tomb Raider', 'rise-of-the-tomb-raider-xbox-one', '20 Year Celebration Edition', 'Action / Adventure', 'Featuring Lara Croft in her first tomb raiding expedition as she seeks to discover the secret of immortality.', 19.99, 29.99, 26, 'Physical Disc', '2015-11-10', 'Square Enix', 0, 0, 'Active'),
(29, 9, 4, 'Cyberpunk 2077', 'cyberpunk-2077-xbox-one', 'Standard 2-Disc Edition', 'RPG / Open World', 'Cyberpunk 2077 is an open-world, action-adventure story set in Night City, a megalopolis obsessed with power, glamour and body modification.', 29.99, 49.99, 30, 'Physical Disc', '2020-12-10', 'CD Projekt RED', 0, 1, 'Active'),
(30, 9, 4, 'Grand Theft Auto V', 'grand-theft-auto-v-xbox-one', 'Premium Physical Edition', 'Action / Open World', 'When a young street hustler, a retired bank robber and a terrifying psychopath find themselves entangled with some of the most frightening criminal elements.', 29.99, 39.99, 40, 'Physical Disc', '2014-11-18', 'Rockstar Games', 0, 1, 'Active'),

-- Xbox Series X|S Games (Products 31-40) [platform_id: 3, category_id: 8]
(31, 8, 3, 'Forza Motorsport', 'forza-motorsport-xbox-series-x', 'Standard Physical Edition', 'Racing / Simulation', 'Out-build the competition using over 800 performance upgrades in the all-new, fun and rewarding single-player career mode.', 69.99, NULL, 35, 'Physical Disc', '2023-10-10', 'Xbox Game Studios', 1, 1, 'Active'),
(32, 8, 3, 'Halo Infinite', 'halo-infinite-xbox-series-x', 'Steelbook Physical Edition', 'First-Person Shooter', 'When all hope is lost and humanity\'s fate hangs in the balance, the Master Chief is ready to confront the most ruthless foe on Zeta Halo.', 39.99, 59.99, 28, 'Physical Disc', '2021-12-08', 'Xbox Game Studios', 0, 1, 'Active'),
(33, 8, 3, 'Starfield', 'starfield-xbox-series-x', 'Standard Physical Edition', 'RPG / Sci-Fi', 'In this next-generation role-playing game set amongst the stars, create any character you want and explore with unparalleled freedom.', 69.99, NULL, 45, 'Physical Disc', '2023-09-06', 'Bethesda Softworks', 1, 1, 'Active'),
(34, 8, 3, 'Forza Horizon 5', 'forza-horizon-5-xbox-series-x', 'Standard Physical Edition', 'Open World Racing', 'Your Ultimate Horizon Adventure awaits! Explore the vibrant and ever-evolving open world landscapes of Mexico with limitless driving action.', 59.99, 69.99, 40, 'Physical Disc', '2021-11-09', 'Xbox Game Studios', 0, 1, 'Active'),
(35, 8, 3, 'Microsoft Flight Simulator', 'microsoft-flight-simulator-xbox-series-x', 'Standard Physical Edition', 'Simulation / Aviation', 'From light planes to wide-body jets, fly highly detailed and accurate aircraft in the next generation of Microsoft Flight Simulator.', 59.99, NULL, 20, 'Physical Disc', '2021-07-27', 'Xbox Game Studios', 1, 0, 'Active'),
(36, 8, 3, 'Senua\'s Saga: Hellblade II', 'hellblade-2-xbox-series-x', 'Standard Physical Edition', 'Action / Adventure', 'The sequel to the award-winning Hellblade: Senua\'s Sacrifice, Senua returns in a brutal journey of survival through the myth and torment of Viking Iceland.', 69.99, NULL, 30, 'Physical Disc', '2024-05-21', 'Xbox Game Studios', 1, 1, 'Active'),
(37, 8, 3, 'S.T.A.L.K.E.R. 2: Heart of Chornobyl', 'stalker-2-xbox-series-x', 'Standard Physical Edition', 'FPS / Survival Horror', 'Discover the vast Chornobyl Exclusion Zone full of dangerous enemies, deadly anomalies and powerful artifacts.', 69.99, NULL, 32, 'Physical Disc', '2024-09-05', 'GSC Game World', 1, 1, 'Active'),
(38, 8, 3, 'Alan Wake 2', 'alan-wake-2-xbox-series-x', 'Deluxe Physical Edition', 'Survival Horror', 'Saga Anderson arrives to investigate ritualistic murders in a small town. Alan Wake pens a dark story to shape the reality around him.', 59.99, 69.99, 25, 'Physical Disc', '2023-10-27', 'Epic Games', 1, 1, 'Active'),
(39, 8, 3, 'Diablo IV', 'diablo-4-xbox-series-x', 'Standard Physical Edition', 'Action / RPG', 'The endless battle between the High Heavens and the Burning Hells rages on as chaos threatens to consume Sanctuary.', 59.99, 69.99, 36, 'Physical Disc', '2023-06-06', 'Blizzard Entertainment', 1, 1, 'Active'),
(40, 8, 3, 'Dragon\'s Dogma 2', 'dragons-dogma-2-xbox-series-x', 'Standard Physical Edition', 'Action / RPG', 'Dragon\'s Dogma 2 is a narrative-driven action-RPG that challenges the players to choose their own experience.', 69.99, NULL, 30, 'Physical Disc', '2024-03-22', 'Capcom', 1, 1, 'Active');

-- -------------------------------------------------------------
-- 2. INSERT PRODUCT IMAGES (Exact paths matching 40 downloaded files)
-- -------------------------------------------------------------
INSERT INTO `product_images` (`image_id`, `product_id`, `image_path`, `alt_text`, `is_primary`, `display_order`) VALUES
(1, 1, 'assets/images/products/ps4-plague-tale.png', 'A Plague Tale: Innocence PS4 Box Art', 1, 1),
(2, 2, 'assets/images/products/ps4-ghost-of-tsushima.png', 'Ghost of Tsushima PS4 Box Art', 1, 1),
(3, 3, 'assets/images/products/ps4-the-last-of-us.png', 'The Last of Us Remastered PS4 Box Art', 1, 1),
(4, 4, 'assets/images/products/ps4-bloodborne.png', 'Bloodborne PS4 Box Art', 1, 1),
(5, 5, 'assets/images/products/ps4-god-of-war.png', 'God of War (2018) PS4 Box Art', 1, 1),
(6, 6, 'assets/images/products/ps4-uncharted-4.png', 'Uncharted 4: A Thief\'s End PS4 Box Art', 1, 1),
(7, 7, 'assets/images/products/ps4-horizon-zero-dawn.png', 'Horizon Zero Dawn PS4 Box Art', 1, 1),
(8, 8, 'assets/images/products/ps4-spiderman.png', 'Marvel\'s Spider-Man PS4 Box Art', 1, 1),
(9, 9, 'assets/images/products/ps4-rdr2.png', 'Red Dead Redemption 2 PS4 Box Art', 1, 1),
(10, 10, 'assets/images/products/ps4-witcher-3.png', 'The Witcher 3: Wild Hunt PS4 Box Art', 1, 1),

(11, 11, 'assets/images/products/ps5-spiderman-2.png', 'Marvel\'s Spider-Man 2 PS5 Box Art', 1, 1),
(12, 12, 'assets/images/products/ps5-gow-ragnarok.png', 'God of War Ragnarök PS5 Box Art', 1, 1),
(13, 13, 'assets/images/products/ps5-ac-mirage.png', 'Assassin\'s Creed Mirage PS5 Box Art', 1, 1),
(14, 14, 'assets/images/products/ps5-demons-souls.png', 'Demon\'s Souls PS5 Box Art', 1, 1),
(15, 15, 'assets/images/products/ps5-ffxvi.png', 'Final Fantasy XVI PS5 Box Art', 1, 1),
(16, 16, 'assets/images/products/ps5-horizon-forbidden-west.png', 'Horizon Forbidden West PS5 Box Art', 1, 1),
(17, 17, 'assets/images/products/ps5-ratchet-clank.png', 'Ratchet & Clank: Rift Apart PS5 Box Art', 1, 1),
(18, 18, 'assets/images/products/ps5-returnal.png', 'Returnal PS5 Box Art', 1, 1),
(19, 19, 'assets/images/products/ps5-re4-remake.png', 'Resident Evil 4 Remake PS5 Box Art', 1, 1),
(20, 20, 'assets/images/products/ps5-elden-ring.png', 'Elden Ring PS5 Box Art', 1, 1),

(21, 21, 'assets/images/products/xbox-one-gears-5.png', 'Gears 5 Xbox One Box Art', 1, 1),
(22, 22, 'assets/images/products/xbox-one-forza-horizon-4.png', 'Forza Horizon 4 Xbox One Box Art', 1, 1),
(23, 23, 'assets/images/products/xbox-one-halo-5.png', 'Halo 5: Guardians Xbox One Box Art', 1, 1),
(24, 24, 'assets/images/products/xbox-one-quantum-break.png', 'Quantum Break Xbox One Box Art', 1, 1),
(25, 25, 'assets/images/products/xbox-one-sunset-overdrive.png', 'Sunset Overdrive Xbox One Box Art', 1, 1),
(26, 26, 'assets/images/products/xbox-one-sea-of-thieves.png', 'Sea of Thieves Xbox One Box Art', 1, 1),
(27, 27, 'assets/images/products/xbox-one-ori.png', 'Ori and the Will of the Wisps Xbox One Box Art', 1, 1),
(28, 28, 'assets/images/products/xbox-one-tomb-raider.png', 'Rise of the Tomb Raider Xbox One Box Art', 1, 1),
(29, 29, 'assets/images/products/xbox-one-cyberpunk.png', 'Cyberpunk 2077 Xbox One Box Art', 1, 1),
(30, 30, 'assets/images/products/xbox-one-gta-v.png', 'Grand Theft Auto V Xbox One Box Art', 1, 1),

(31, 31, 'assets/images/products/xbox-series-forza-motorsport.png', 'Forza Motorsport Xbox Series X Box Art', 1, 1),
(32, 32, 'assets/images/products/xbox-series-halo-infinite.png', 'Halo Infinite Xbox Series X Box Art', 1, 1),
(33, 33, 'assets/images/products/xbox-series-starfield.png', 'Starfield Xbox Series X Box Art', 1, 1),
(34, 34, 'assets/images/products/xbox-series-forza-horizon-5.png', 'Forza Horizon 5 Xbox Series X Box Art', 1, 1),
(35, 35, 'assets/images/products/xbox-series-flight-sim.png', 'Microsoft Flight Simulator Xbox Series X Box Art', 1, 1),
(36, 36, 'assets/images/products/xbox-series-hellblade-2.png', 'Hellblade II Xbox Series X Box Art', 1, 1),
(37, 37, 'assets/images/products/xbox-series-stalker-2.png', 'S.T.A.L.K.E.R. 2 Xbox Series X Box Art', 1, 1),
(38, 38, 'assets/images/products/xbox-series-alan-wake-2.png', 'Alan Wake 2 Xbox Series X Box Art', 1, 1),
(39, 39, 'assets/images/products/xbox-series-diablo-4.png', 'Diablo IV Xbox Series X Box Art', 1, 1),
(40, 40, 'assets/images/products/xbox-series-dragons-dogma-2.png', 'Dragon\'s Dogma 2 Xbox Series X Box Art', 1, 1);

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
