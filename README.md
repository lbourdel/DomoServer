# DomoServer

To clone only scheduler folder, do in empty folder:

git init DomoScheduler
cd DomoScheduler/
git remote add origin git@github.com:lbourdel/DomoServer.git
git config core.sparsecheckout true
echo "domocan/scheduler/*"  >> .git/info/sparse-checkout
git pull --depth=1 origin master

