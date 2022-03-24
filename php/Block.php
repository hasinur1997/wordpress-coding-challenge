<?php
/**
 * Block class.
 *
 * @package SiteCounts
 */

namespace XWP\SiteCounts;

use WP_Block;
use WP_Query;

/**
 * The Site Counts dynamic block.
 *
 * Registers and renders the dynamic block.
 */
class Block {

	/**
	 * The Plugin instance.
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Instantiates the class.
	 *
	 * @param Plugin $plugin The plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Adds the action to register the block.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_block' ] );
	}

	/**
	 * Registers the block.
	 */
	public function register_block() {
		register_block_type_from_metadata(
			$this->plugin->dir(),
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);
	}

    /**
	 * Renders the block.
	 *
	 * @param array    $attributes The attributes for the block.
	 * @param string   $content    The block content, if any.
	 * @param WP_Block $block      The instance of this block.
	 * @return string The markup of the block.
	 */
	public function render_callback( $attributes, $content, $block ) {
		$post_types = get_post_types( array( 'public' => true ) );
		$class_name = $attributes['className'];
		ob_start();

		?>
		<div class="<?php echo esc_attr( $class_name ); ?>">
			<h2>Post Counts</h2>
			<ul>
			<?php
			foreach ( $post_types as $post_type_slug ) :
				$post_type_object = get_post_type_object( $post_type_slug );
				$post_count       = count(
					get_posts(
						array(
							'post_type'      => $post_type_slug,
							'posts_per_page' => -1,
						)
					)
				);

				?>
				<li>
					<?php
						printf(
							/* translators: 1: Number of Post, 2: Post type name. */
							esc_html__( 'There are %1$s %2$s.', 'brodo' ),
							esc_attr( $post_count ),
							esc_attr( $post_type_object->labels->name )
						);
					?>
				</li>
			<?php endforeach; ?>
			</ul>
				<p>
					<?php
						$post_id = ! empty( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
						/* translators: 1: Post ID. */
						printf( esc_html__( 'The current post ID is %s.', 'brodo' ), esc_attr( $post_id ) );
					?>
				</p>

			<?php
			$query = new WP_Query(
				array(
					'post_type'     => array( 'post', 'page' ),
					'post_status'   => 'any',
					'date_query'    => array(
						array(
							'hour'    => 9,
							'compare' => '>=',
						),
						array(
							'hour'    => 17,
							'compare' => '<=',
						),
					),
					'tag'           => 'foo',
					'category_name' => 'baz',
					'post__not_in'  => array( get_the_ID() ),
				)
			);

			if ( $query->have_posts() ) :
				?>
					<h2><?php esc_html_e( '5 posts with the tag of foo and the category of baz', 'brodo' ); ?></h2>
				<ul>
				<?php

				foreach ( array_slice( $query->posts, 0, 5 ) as $post ) :
					?>
					<li><?php echo esc_attr( $post->post_title ); ?></li>
					<?php
				endforeach;
			endif;
			?>
			</ul>
		</div>
		<?php

		return ob_get_clean();
	}
}
