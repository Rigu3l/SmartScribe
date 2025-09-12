# 🚀 SmartScribe Deployment to Render (Free Tier)

This guide walks you through deploying SmartScribe to Render's free tier using Railway for the MySQL database.

## Prerequisites
- GitHub account (for connecting to Render)
- Railway account (for free MySQL database)
- Google Gemini API key
- Google OAuth credentials

## Step 1: Set Up Railway Database
1. Go to [Railway.app](https://railway.app) and sign up for free
2. Create a new project
3. Add a MySQL database
4. Note down the database credentials (host, user, password, database name)

## Step 2: Prepare Your Code
The project is already prepared with:
- Built Vue.js frontend in `dist/` folder
- Dockerfile for PHP backend
- Production `.env` file with placeholders

## Step 3: Push to GitHub
1. Create a new repository on GitHub
2. Push your SmartScribe code to the repository

## Step 4: Deploy Backend to Render
1. Go to [Render.com](https://render.com) and sign up for free
2. Create a new Web Service
3. Connect your GitHub repository
4. Configure the service:
   - **Name**: smartscribe-api
   - **Environment**: Docker
   - **Branch**: main (or your deployment branch)
   - **Build Command**: (leave empty, Dockerfile handles it)
   - **Start Command**: (leave empty, Dockerfile handles it)

5. Add Environment Variables:
   - `DB_HOST`: Your Railway MySQL host
   - `DB_NAME`: Your Railway database name
   - `DB_USER`: Your Railway database user
   - `DB_PASS`: Your Railway database password
   - `GOOGLE_GEMINI_API_KEY`: Your production API key
   - `JWT_SECRET`: Generate a 64-character random string
   - `APP_ENV`: production
   - `APP_DEBUG`: false
   - `GOOGLE_OAUTH_CLIENT_ID`: Your production OAuth client ID
   - `GOOGLE_CLIENT_ID`: Your production client ID
   - `GOOGLE_CLIENT_SECRET`: Your production client secret
   - `VUE_APP_GOOGLE_OAUTH_CLIENT_ID`: Your production OAuth client ID

6. Deploy the service

## Step 5: Deploy Frontend to Render
1. Create a new Static Site on Render
2. Connect the same GitHub repository
3. Configure:
   - **Name**: smartscribe-frontend
   - **Branch**: main
   - **Build Command**: `npm install && npm run build`
   - **Publish Directory**: `dist`

4. Add Environment Variable:
   - `VUE_APP_API_BASE_URL`: https://your-backend-service.onrender.com/api

5. Deploy the static site

## Step 6: Run Database Migrations
1. Once backend is deployed, access your backend URL
2. Run migrations by visiting: `https://your-backend-service.onrender.com/api/run_production_migration.php`
3. This will execute all necessary database migrations for SmartScribe

## Step 7: Update Frontend API URL
Update the frontend's API base URL in the deployed static site settings if needed.

## Step 8: Configure Custom Domain (Optional)
- In Render dashboard, add your custom domain
- Update DNS records as instructed

## Testing
1. Access your frontend URL
2. Try user registration
3. Test note creation
4. Verify API calls work

## Free Tier Limitations
- Render: 750 hours/month, sleeps after inactivity
- Railway: 512MB RAM, 1GB storage

## Troubleshooting
- Check Render logs for errors
- Verify environment variables are set correctly
- Ensure database is accessible from Render
- Test API endpoints manually

Your SmartScribe app should now be live on Render's free tier!