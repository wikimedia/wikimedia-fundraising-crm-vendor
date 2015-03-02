/*!
 * Grunt file
 *
 * @package DonationInterface
 */

/*jshint node:true */
module.exports = function ( grunt ) {
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-jscs-checker' );

	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),
		jshint: {
			options: {
				jshintrc: true
			},
			shared: [
				'modules/*.js',
				'modules/js/*.js',
				'gateway_forms/rapidhtml/*/*.js'
			],
			tests: 'tests/*/*.js',
			gateways: '{adyen,amazon,globalcollect,paypal,worldpay}_gateway/forms/**/*.js'
		},
		jscs: {
			shared: { src: '<%= jshint.shared %>' },
			tests: { src: '<%= jshint.tests %>' },
			gateways: { src: '<%= jshint.gateways %>' }
		},
		banana: {
			shared: 'gateway_common/i18n/*/',
			gateways: '{adyen,amazon,globalcollect,paypal,worldpay}_gateway/i18n/'
		},
		watch: {
			files: [
				'.{jscsrc,jshintignore,jshintrc}',
				'<%= jshint.shared %>',
				'<%= jshint.tests %>',
				'<%= jshint.gateways %>'
			],
			tasks: 'test'
		}
	} );

	grunt.registerTask( 'lint', [ 'jshint', 'jscs', 'banana' ] );
	grunt.registerTask( 'test', [ 'lint' ] );
	grunt.registerTask( 'default', 'test' );
};
