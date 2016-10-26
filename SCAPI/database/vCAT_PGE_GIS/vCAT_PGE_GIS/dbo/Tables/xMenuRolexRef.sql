CREATE TABLE [dbo].[xMenuRolexRef] (
    [rMenuRoleXrefID]   INT           IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [LightHouseMenuUID] VARCHAR (100) NULL,
    [RoleUID]           VARCHAR (100) NULL,
    [ActiveFlag]        BIT           NOT NULL
);

