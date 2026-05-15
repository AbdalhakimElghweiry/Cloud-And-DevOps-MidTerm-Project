Midterm Project: Message Queue + Visit Counter with Redis 
Cloud Computing and DevOps Engineering – Week 8 
Instructor: M.Sc. Abdelhakim Rashid 
Names:Abdalhakim Awad Elghweiry, Wael Mohamed BenEdris
ID’s:5061, 4886


1. # Project Overview :
DevOpsHub Is A Project About Two Apps That Don't Share Data With Each Other But They Use An Image Of Redis To Store Data In And They Access The Data In Redis.

/////////////////////////////////////////////////////////////////////////

2. # File Structure And Architecture :

Our initial File Structure Was A Mess And Too Complicated For A Simple Project Like This,  So We Decided To Make It More Simple By Reducing Unnecessary Files And To Organize It Like This :

Cloud-and-DevOps-Project
├──app1
│  ├──index.php
│  ├── Dockerfile
│
├──app2
│  ├──index.php
│  ├── Dockerfile
├──docker-compose.yml
└──readme.md

/////////////////////////////////////////////////////////////////////////

3. # Explanation of how the two apps interact with Redis
As it is explained in the project overview , and in the architecture, the app1 and app2 are two sepradted apps and don't have acceses to each other and don't use data from each other, but they use The Redis Image To Store Data Inside It .

App1 Collects The Massages And Visit Counts And Stores Them In Redis, And App2 Sees The Data Stored In Redis Image By App1 And SHows Is In The Dashboard . 
 
# In Simple Words App1 Does The Work And Tills Redis  To Save It, And App2 Shows What App1 Did By Looking On What'S Saved IN Redis .

/////////////////////////////////////////////////////////////////////////

4. # Prerequisites 
 4.1 Must Have Docker Desktop With A DockerHub Accout And It Must Be Open And Running .
 4.2 Check Docker Is Installed And Know The Version By Typing The Follwoing Command In CMD or PowerShell:

  ```bash
  docker --version
  docker compose version
  ```
 # Expected output:
 # Docker version 26.x.x, build xxxxx
 # Docker Compose version v2.x.x
 4.3 Clone The Repo From The Url Provided By US IN This File :
 https://github.com/AbdalhakimElghweiry/Cloud-And-DevOps-MidTerm-Project
 4.4 You Must Have A Github or Gitlab Account And You Must Be Signed In.
 
/////////////////////////////////////////////////////////////////////////

5. # Step by step solution 
5.1 First IN VsCode Terminal (Or Any Terminal You Have) Clone The Repo From This Link () By Typing THE EXCACT FOLLOWING COMMAND : 

# git clone  https://github.com/AbdalhakimElghweiry/Cloud-And-DevOps-MidTerm-Project

5.2 After You Clone The Repo, You Will Have All The Files Need to Run The Project In Your Laptop. 
# PS: Make Sure It's The Same File Structure Shown In The File.
5.3 Now Open Docker Desktop And Make Sure The Engine Is Running.
5.4 Now In Your VsCode Terminal (Or Any Terminal You Have) Type The Following Command :

# docker compose up -d ( -d is for running in background, you can skip it)
# PS : We Use Docker Compose To Run All Services With One Command, If You Dont Use It You Will Have To Start Everything One By One In The Terminal.
 5.4.2 After You Run The Command, Docker Will Look Into The docker-compose.yml File And Read It And Know What To Do By It Self .
 5.4.3  If You Did Everthing Right You Should Se Somthing Like This :
 [+] Running 4/4
  Network devopshub-net         Created
  Container devopshub-redis      Started
  Container devopshub-app1       Started
  Container devopshub-app2       Started   

5.5 To Make Sure All Services Are Up And Running Type The Following Command  In Your VsCode Terminal Or Any Terminal You Have:

# docker compose ps
Should Se Somthing Like This : 
Name        Image      Command       Ports      sTATUS    
And All Of Your Services Will Show Up Here. 

5.6 Finaly Now You Can Visit The Thes Links To Use The Apps : 
http://localhost:5001 ( 5001 Port IS For App 1)
http://localhost:5002 ( 5002 Port IS For App 2)


/////////////////////////////////////////////////////////////////////////

And That's All My Fellow Engineers In The Whole Wide World .
Try It And Till Us What Do You Think, And If You Have Any Notes Or Comments Feel Free To Email Us At :

Abdalhakim : kimboelgwere@gmail.com
Wael : Wael_4886@limu.edu.ly

Until Next Time. 👋🏻