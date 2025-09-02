const { defineConfig } = require('@vue/cli-service')
const webpack = require('webpack')

module.exports = defineConfig({
  transpileDependencies: true,
  devServer: {
    proxy: {
      '/api': {
        target: 'http://localhost',
        changeOrigin: true,
        pathRewrite: {
          '^/api': '/SmartScribe-main/public'
        },
        onProxyReq: (proxyReq, req) => {
          // Forward Authorization header
          if (req.headers.authorization) {
            proxyReq.setHeader('Authorization', req.headers.authorization);
          }
          // Forward other headers
          if (req.headers['x-user-id']) {
            proxyReq.setHeader('X-User-ID', req.headers['x-user-id']);
          }
        }
      }
    },
    headers: {
      'Cache-Control': 'no-cache, no-store, must-revalidate',
      'Pragma': 'no-cache',
      'Expires': '0'
    }
  },
  configureWebpack: {
    plugins: [
      new webpack.DefinePlugin({
        __VUE_OPTIONS_API__: JSON.stringify(true),
        __VUE_PROD_DEVTOOLS__: JSON.stringify(false),
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: JSON.stringify(false),
        // Additional Vue 3 feature flags
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: JSON.stringify(false),
        __VUE_I18N_FULL_INSTALL__: JSON.stringify(false),
        __VUE_I18N_LEGACY_API__: JSON.stringify(false),
        __VUE_ROUTER_PROD_DEVTOOLS__: JSON.stringify(false)
      })
    ]
  }
})
