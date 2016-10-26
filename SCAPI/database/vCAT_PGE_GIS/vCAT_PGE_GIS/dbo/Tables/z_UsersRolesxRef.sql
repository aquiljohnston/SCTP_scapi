CREATE TABLE [dbo].[z_UsersRolesxRef] (
    [rUserRolesxRefID] INT           IDENTITY (1, 1) NOT NULL,
    [UserUID]          VARCHAR (100) NULL,
    [RoleUID]          VARCHAR (100) NULL,
    [ActiveFlag]       BIT           NULL
);

