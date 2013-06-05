<?php 
/**
 * The template for displaying Search Results pages.
 *
 * @package WordPress
 * @subpackage Green_Is_Good
 * @since Green Is Good 1.0
 */
get_header(); ?>
		<div id="body">
			<div class="container container_2 clearfix">
				<div class='grid_1' style="width: 719px; padding-right: 15px;">

					<?php include (TEMPLATEPATH . '/searchform.php'); ?>
					<br />
					<?php if ( have_posts() ) : $count = 0; ?>
					<div id="results-header">
						<h3><span>Results</span></h3>
						<span id="results-header-details">You searched for "<?php echo get_search_query() ?>" and got <?php echo $wp_query->found_posts?> results.</span>
					</div>
					
					<div id="posts">		
					<?php while (have_posts()) : the_post(); $count++; ?>
<div class="post homepost">
							<h1><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a></h1>
							<div class="container container_2 clearfix">
								<div class='grid_1 image' style="width: 220px;">
									<?php echo_first_image(get_the_ID(), 220); ?>
								</div>
								<div class='grid_1 postpreview'>
									<div id="postmetasocial">
										<div class="post_info">
											By <span class="author_name"><?php the_author_posts_link(); ?></span> | <?php the_time('M j, Y'); ?> | 
											<?php 
												$categories = get_the_category();
												if (is_array($categories) && count($categories) > 0) {
													$category = $categories[0]->name;
													?>
													<span class="post_category"><a href="<?php echo get_category_link($categories[0]->cat_ID) ?>"><?php echo $category ?></a></span>
													<?php
												}
											?>
										</div><!-- post_info -->
										<div class='social'>
											<div class="facebook-likes">
												<iframe src="http://www.facebook.com/plugins/like.php?href=<?php the_permalink() ?>&amp;send=false&amp;layout=button_count&amp;width=90&amp;show_faces=true&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:90px; height:21px;" allowTransparency="true"></iframe>
											</div>
											<div class="twitter-tweets">
												<a href="https://twitter.com/share" class="twitter-share-button" data-text="<?php the_title(); ?>" data-via="e27sg" data-url="<?php the_permalink() ?>">Tweet</a>
											</div>
										</div><!-- social -->		
									</div><!-- postmetasocial -->
									<div class="post_preview">										
										<?php the_excerpt(); ?>
									</div><!-- post_preview -->
								</div><!-- grid_1 postpreview -->
								<div class="nav-next-btn fr">
									<span class="post_read_more"><a href="<?php the_permalink() ?>">Read More</a></span>
								</div>
							</div>
						</div>		
						<?php if ($count == 5) : ?>
				            <?php include( TEMPLATEPATH . '/includes/ads-2x300.php' ); ?>
			            <?php endif; ?>
					<?php endwhile; ?> 
					</div>
					<?php
						pagination($wp_query->max_num_pages); 
					?>
					<?php else : ?>
					<div id="results-header">
						<h1>Results</h1>
						<span id="results-header-details">Sorry, but nothing matched your search criteria. Please try again with some different keywords.</span>
					</div>
					<?php endif; ?>
					
					
					<?php 
					/**										
					<div id="posts">
						<div class="post first">
							<h1>A city without bookshops</h1>
							<div class="container container_2 clearfix">
								<div class='grid_1 image' style="width: 180px;"><a href=""><img src="<?php bloginfo('template_directory') ?>/img/post-image-sample.png" /></a></div>
								<div class='grid_1 preview' style="width: 400px;">
									<div class="post_info">
										By Joash Wee | March 9, 2012 | <span class="post_category">Business</span>
									</div>
									<div class="post_preview">
										The first time I did it I felt guilty afterwards. I was in Kinokuniya, checking out a book on the science shelves, 
										Complexity: a Guided Tour, by Melanie Mitchell. Complexity, a hefty hardback from Oxford University Press, seemed a bit expensive...
										<div class="post_read_more"><a href="#">Read More &gt;&gt;</a></div>
									</div>
								</div>
								<div class='grid_1 social' style="width: 130px; border-left: 1px solid #000000; padding-left: 5px;">
									<div class="facebook-likes">
										<a href="#"><img src="<?php bloginfo('template_directory') ?>/img/facebook-likes.png" /></a>
									</div>
									<div class="twitter-tweets">
										<a href="#"><img src="<?php bloginfo('template_directory') ?>/img/twitter-tweets.png" /></a>
									</div>
									<div class="google_plus-likes">
										<a href="#"><img src="<?php bloginfo('template_directory') ?>/img/google_plus-likes.png" /></a>
									</div>
									<div class="linkedin-share">
										<a href="#"><img src="<?php bloginfo('template_directory') ?>/img/linkedin-share.png" /></a>
									</div>
								</div>
							</div>
						</div>
						
						<div class="sponsored advertisement">
							<img src="<?php bloginfo('template_directory') ?>/img/sponsored-ads.png">
						</div>
						
						<div class="post last">
							<h1>A city without bookshops</h1>
							<div class="container container_2 clearfix">
								<div class='grid_1 image' style="width: 180px;"><a href=""><img src="<?php bloginfo('template_directory') ?>/img/post-image-sample.png" /></a></div>
								<div class='grid_1 preview' style="width: 400px;">
									<div class="post_info">
										By Joash Wee | March 9, 2012 | <span class="post_category">Business</span>
									</div>
									<div class="post_preview">
										The first time I did it I felt guilty afterwards. I was in Kinokuniya, checking out a book on the science shelves, 
										Complexity: a Guided Tour, by Melanie Mitchell. Complexity, a hefty hardback from Oxford University Press, seemed a bit expensive...
										<div class="post_read_more"><a href="#">Read More &gt;&gt;</a></div>
									</div>
								</div>
								<div class='grid_1 social' style="width: 130px; border-left: 1px solid #000000; padding-left: 5px;">
									<div class="facebook-likes">
										<a href="#"><img src="<?php bloginfo('template_directory') ?>/img/facebook-likes.png" /></a>
									</div>
									<div class="twitter-tweets">
										<a href="#"><img src="<?php bloginfo('template_directory') ?>/img/twitter-tweets.png" /></a>
									</div>
									<div class="google_plus-likes">
										<a href="#"><img src="<?php bloginfo('template_directory') ?>/img/google_plus-likes.png" /></a>
									</div>
									<div class="linkedin-share">
										<a href="#"><img src="<?php bloginfo('template_directory') ?>/img/linkedin-share.png" /></a>
									</div>
								</div>
							</div>
						</div>
						
					</div>
					<div id="pagination" class="page_nav page_nav_last">
						<ul class="clearfix">
							<!-- <li class="pages"><span>Page 1 of 2,138</span></li>  -->
							<li class="prev"><!-- <a href="http://mashable.com/">Prev</a> --></li>
							<li class="active"><span>1</span></li>
							<li><a title="2" class="page" href="http://mashable.com/page/2/">2</a></li>
							<li><a title="3" class="page" href="http://mashable.com/page/3/">3</a></li>
							<li><a title="4" class="page" href="http://mashable.com/page/4/">4</a></li>
							<li><a title="5" class="page" href="http://mashable.com/page/5/">5</a></li>
							<li><a title="6" class="page" href="http://mashable.com/page/6/">6</a></li>
							<li><a title="7" class="page" href="http://mashable.com/page/7/">7</a></li>
							<li><a title="8" class="page" href="http://mashable.com/page/8/">8</a></li>
							<li class="next"><a href="http://mashable.com/page/2/">Next &gt;&gt;</a></li>
						</ul>
						<span class="right">
							<a href="#" class="back_to_top">Back to top</a>
							<!-- 
					    	<a href="/tag/" data-id="topics" class="buttons toggle-div">Topics</a>
					    	<a href="#" data-id="list" class="buttons toggle-div">Lists</a>
						    <a href="#" data-id="news" class="buttons toggle-div">News</a>
						     -->
					 	</span>
					</div>
					 */
					?>
				</div>
				<?php get_sidebar(); ?>
			</div>
		</div>
<?php get_footer(); ?>