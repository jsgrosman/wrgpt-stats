const path = require('path');

module.exports = {
    entry: {
        player: './src/player.js',
        table: './src/table.js',
    },
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, 'dist')
    },
    module: {
        rules: [
            // @see https://reactkungfu.com/2015/10/integrating-jquery-chosen-with-webpack-using-imports-loader/
            { test: /chosen.jquery.js$/, use: 'imports-loader?jQuery=jquery,$=jquery,this=>window' },
        ]
    }
};
