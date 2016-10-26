CREATE TABLE [dbo].[rLightHouseMenu] (
    [LightHouseMenuID]  INT            IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [LightHouseMenuUID] VARCHAR (100)  NULL,
    [ProjectID]         INT            NOT NULL,
    [CreatedUserUID]    VARCHAR (100)  NOT NULL,
    [ModifiedUserUID]   VARCHAR (100)  NOT NULL,
    [CreateDTLT]        DATETIME       CONSTRAINT [DF_r_LightHouseMenus_CreateDTLT] DEFAULT (getdate()) NULL,
    [ModifiedDTLT]      DATETIME       NULL,
    [InactiveDTLT]      DATETIME       NULL,
    [Comments]          VARCHAR (2000) NULL,
    [Revision]          INT            CONSTRAINT [DF_r_LightHouseMenus_Revision] DEFAULT ((0)) NOT NULL,
    [ActiveFlag]        BIT            NOT NULL,
    [MenuName]          VARCHAR (50)   NULL,
    [ParentUID]         VARCHAR (100)  NULL,
    [SortSeq]           INT            NULL,
    [DisplayType]       VARCHAR (50)   NULL,
    [Action]            VARCHAR (200)  NULL,
    CONSTRAINT [PK_r_LightHouseMenus] PRIMARY KEY CLUSTERED ([LightHouseMenuID] ASC)
);

