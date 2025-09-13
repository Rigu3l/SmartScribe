const webpack = require('webpack');

module.exports = {
  devServer: {
    proxy: {
      '/SmartScribe/api': {
        target: 'http://localhost/',
        changeOrigin: true,
        secure: false,
        logLevel: 'debug',
        pathRewrite: (path, req) => {
          // Preserve query parameters when rewriting the path
          const queryString = req.url.split('?')[1];
          const newPath = '/SmartScribe-main/api/index.php';
          return queryString ? `${newPath}?${queryString}` : newPath;
        },
        onProxyReq: (proxyReq, req, res) => {
          console.log('🚀 PROXY:', req.method, req.url, '->', proxyReq.getHeader('host') + proxyReq.path);
        }
      },
      '/api': {
        target: 'http://localhost/',
        changeOrigin: true,
        secure: false,
        logLevel: 'debug',
        pathRewrite: (path, req) => {
          // Preserve query parameters when rewriting the path
          const queryString = req.url.split('?')[1];
          const newPath = '/SmartScribe-main/public/index.php';
          return queryString ? `${newPath}?${queryString}` : newPath;
        },
        onProxyReq: (proxyReq, req, res) => {
          console.log('🚀 PROXY:', req.method, req.url, '->', proxyReq.getHeader('host') + proxyReq.path);
        }
      }
    }
  },
  configureWebpack: {
    plugins: [
      new webpack.DefinePlugin({
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: JSON.stringify(false)
      })
    ]
  }
}
