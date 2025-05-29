# Spheria Project Diagrams

## Use Case Diagram

```mermaid
graph TD
    subgraph "Spheria Social Media Platform"
        subgraph "User Management"
            UC1[Register Account]
            UC2[Login]
            UC3[Update Profile]
            UC4[Manage Privacy Settings]
            UC5[Reset Password]
            UC6[Verify Account]
        end
        
        subgraph "Content Management"
            UC7[Create Post]
            UC8[Upload Media]
            UC9[Create Story]
            UC10[Delete Content]
            UC11[Edit Content]
        end
        
        subgraph "Social Interactions"
            UC12[Follow User]
            UC13[Like Post]
            UC14[Comment on Post]
            UC15[Share Post]
            UC16[Save Post]
            UC17[View Stories]
        end
        
        subgraph "Communication"
            UC18[Send Message]
            UC19[Create Conversation]
            UC20[React to Message]
            UC21[View Notifications]
        end
        
        subgraph "Discovery"
            UC22[Search Users]
            UC23[View Feed]
            UC24[Explore Trending]
        end
    end
    
    User((User)) --> UC1
    User --> UC2
    User --> UC3
    User --> UC4
    User --> UC5
    User --> UC6
    User --> UC7
    User --> UC8
    User --> UC9
    User --> UC10
    User --> UC11
    User --> UC12
    User --> UC13
    User --> UC14
    User --> UC15
    User --> UC16
    User --> UC17
    User --> UC18
    User --> UC19
    User --> UC20
    User --> UC21
    User --> UC22
    User --> UC23
    User --> UC24
```

## User Authentication Flow

```mermaid
flowchart TD
    A[Start] --> B{Has Account?}
    B -->|No| C[Register]
    B -->|Yes| D[Login]
    
    C --> E[Enter User Details]
    E --> F[Submit Registration]
    F --> G[Generate OTP]
    G --> H[Send Verification Email]
    H --> I{Verify Email?}
    I -->|Yes| J[Enter OTP]
    I -->|No| K[Resend OTP]
    K --> I
    J --> L{Valid OTP?}
    L -->|Yes| M[Account Verified]
    L -->|No| N[Try Again]
    N --> J
    
    D --> O[Enter Credentials]
    O --> P{Valid Credentials?}
    P -->|Yes| Q[Generate Session]
    P -->|No| R[Error Message]
    R --> S{Forgot Password?}
    S -->|Yes| T[Password Reset]
    S -->|No| O
    
    T --> U[Enter Email]
    U --> V[Send Reset Link]
    V --> W[Enter New Password]
    W --> X[Update Password]
    X --> O
    
    Q --> Y[Redirect to Feed]
    M --> Y
    
    Y --> Z[End]
```

## Post Creation and Interaction Flow

```mermaid
flowchart TD
    A[Start] --> B[User Logged In]
    B --> C[Create New Post]
    
    C --> D[Enter Post Content]
    D --> E{Add Media?}
    E -->|Yes| F[Upload Media]
    E -->|No| G[Preview Post]
    F --> G
    
    G --> H{Confirm Post?}
    H -->|Yes| I[Submit Post]
    H -->|No| J[Edit Post]
    J --> G
    
    I --> K[Store in Database]
    K --> L[Update Feed]
    L --> M[Notify Followers]
    
    N[View Post] --> O{User Action}
    O -->|Like| P[Update Like Count]
    O -->|Comment| Q[Add Comment]
    O -->|Share| R[Share Options]
    O -->|Save| S[Add to Saved Posts]
    
    P --> T[Update UI]
    Q --> U[Display Comment]
    R --> V[Process Share]
    S --> W[Confirm Save]
    
    T --> X[End]
    U --> X
    V --> X
    W --> X
```

## Messaging System Flow

```mermaid
flowchart TD
    A[Start] --> B[User Logged In]
    B --> C[Open Inbox]
    
    C --> D{Existing Conversation?}
    D -->|Yes| E[Select Conversation]
    D -->|No| F[Create New Conversation]
    
    F --> G[Search User]
    G --> H[Select Recipient]
    H --> I[Initialize Conversation]
    I --> J[Open Chat Interface]
    
    E --> J
    
    J --> K[View Message History]
    K --> L[Compose Message]
    
    L --> M{Include Media?}
    M -->|Yes| N[Upload Media]
    M -->|No| O[Preview Message]
    N --> O
    
    O --> P[Send Message]
    P --> Q[Store in Database]
    Q --> R[Update Chat Interface]
    R --> S[Send Notification]
    
    T[Recipient] --> U[Receive Notification]
    U --> V[Open Conversation]
    V --> W[View New Message]
    W --> X{React to Message?}
    X -->|Yes| Y[Select Reaction]
    X -->|No| Z[Compose Reply]
    
    Y --> AA[Update Message]
    Z --> L
    
    AA --> AB[End]
```

## Story Creation and Viewing Flow

```mermaid
flowchart TD
    A[Start] --> B[User Logged In]
    B --> C{Create or View?}
    
    C -->|Create| D[Create New Story]
    C -->|View| E[View Stories]
    
    D --> F[Capture/Upload Media]
    F --> G[Add Effects/Text]
    G --> H[Set Privacy]
    H --> I[Set Duration]
    I --> J[Preview Story]
    J --> K{Confirm?}
    K -->|Yes| L[Publish Story]
    K -->|No| M[Edit Story]
    M --> J
    
    L --> N[Store in Database]
    N --> O[Set Expiry Timer]
    O --> P[Notify Followers]
    
    E --> Q[Display Story Circles]
    Q --> R[Select User Story]
    R --> S[Display Story]
    S --> T[Mark as Viewed]
    T --> U{More Stories?}
    U -->|Yes| V[Next Story]
    U -->|No| W[Return to Feed]
    
    V --> S
    
    P --> X[End]
    W --> X
```

## Search and Discovery Flow

```mermaid
flowchart TD
    A[Start] --> B[User Logged In]
    B --> C[Access Search]
    
    C --> D[Enter Search Query]
    D --> E[Process Search]
    
    E --> F[Query Database]
    F --> G[Display Results]
    G --> H[Save to Search History]
    
    H --> I{Filter Results?}
    I -->|Yes| J[Apply Filters]
    I -->|No| K{Select Result?}
    
    J --> G
    
    K -->|Yes| L[View Profile/Content]
    K -->|No| M{New Search?}
    
    M -->|Yes| D
    M -->|No| N[End Search]
    
    L --> O{Follow/Interact?}
    O -->|Yes| P[Process Action]
    O -->|No| Q[Return to Results]
    
    P --> Q
    Q --> K
    
    N --> R[End]
```