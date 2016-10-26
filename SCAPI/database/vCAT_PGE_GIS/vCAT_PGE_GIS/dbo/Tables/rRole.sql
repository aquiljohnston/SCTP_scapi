CREATE TABLE [dbo].[rRole] (
    [rRoleID]         INT            IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [RoleUID]         VARCHAR (100)  NULL,
    [ProjectID]       INT            NULL,
    [CreatedUserUID]  VARCHAR (1100) CONSTRAINT [DF_rRole_CreatedUserUID] DEFAULT (getdate()) NULL,
    [ModifiedUserUID] VARCHAR (100)  NULL,
    [CreateDTLT]      DATETIME       CONSTRAINT [DF_r_Roles_CreateDate] DEFAULT (getdate()) NULL,
    [ModifiedDTLT]    DATETIME       NULL,
    [Comments]        VARCHAR (2000) NULL,
    [Revision]        INT            CONSTRAINT [DF_r_Roles_Revision] DEFAULT ((0)) NOT NULL,
    [ActiveFlag]      BIT            CONSTRAINT [DF_r_Roles_ActiveFlag] DEFAULT ((1)) NOT NULL,
    [RoleLevelType]   VARCHAR (50)   NULL,
    [RoleName]        VARCHAR (50)   NULL,
    [RoleDescription] VARCHAR (500)  NULL,
    [RoleSortSeq]     SMALLINT       NULL,
    CONSTRAINT [PK_r_Roles] PRIMARY KEY CLUSTERED ([rRoleID] ASC)
);

