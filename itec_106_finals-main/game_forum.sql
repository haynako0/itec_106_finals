SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);


ALTER TABLE `deleted_comments`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `deleted_flairs`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `deleted_posts`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `deleted_users`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `game_flairs`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `post_flairs`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `post_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `post_id` (`post_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);


ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);


ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `deleted_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `deleted_flairs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `deleted_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `deleted_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `game_flairs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `post_flairs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `post_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


INSERT INTO `users` (`id`, `username`, `password`, `is_moderator`, `is_admin`) VALUES
(1, 'admin', '1234', 0, 1),
(2, 'moderator', '5678', 1, 0);


INSERT INTO `post_flairs` (`id`, `name`) VALUES
(1, 'Post Flair TEST');


INSERT INTO `game_flairs` (`id`, `name`) VALUES
(1, 'Game Flair TEST');

COMMIT;

