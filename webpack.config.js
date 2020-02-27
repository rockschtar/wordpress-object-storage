const defaultConfig = require("./node_modules/@wordpress/scripts/config/webpack.config");
const path = require('path');

let config = {
    module: {},
};

let jsConfig = Object.assign({}, config, {
    ...defaultConfig,
    entry: {
        ObjectStorageBrowser: './js/ObjectStorageBrowser.jsx',
        Pagination: './js/Pagination.jsx'
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
            {
                test: /.css$/,
                use: [
                    {
                        loader: 'file-loader',
                        options: {name: '[name].css'}
                    },

                    {
                        loader: 'extract-loader'
                    },
                    {
                        loader: 'css-loader'
                    }
                ]
            },
        ],
    },
});

module.exports = [
    jsConfig
];