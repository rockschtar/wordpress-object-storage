const defaultConfig = require("./node_modules/@wordpress/scripts/config/webpack.config");
const path = require('path');

let config = {
    module: {},
};

let jsConfig = Object.assign({}, config, {
    ...defaultConfig,
    entry: {
        index: './js/index.jsx',
        pagination : './js/pagination.jsx'
    },
    output: {
        path: path.resolve(__dirname, 'js/dist'),
        filename: '[name].js',
    },
    resolve: {
        extensions: ['.js', '.jsx'],

    },
    module: {
        rules: [
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: [
                    {loader: 'babel-loader'}]
            },
        ],
    },
});

module.exports = [
    jsConfig
];