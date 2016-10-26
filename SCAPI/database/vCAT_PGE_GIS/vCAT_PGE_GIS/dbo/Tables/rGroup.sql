CREATE TABLE [dbo].[rGroup] (
    [rGroupID]         INT           IDENTITY (1, 1) NOT NULL,
    [GroupUID]         VARCHAR (100) NULL,
    [ProjectID]        INT           NULL,
    [CreatedUserUID]   VARCHAR (100) NULL,
    [ModifiedUserUID]  VARCHAR (100) NULL,
    [CreatedDateTime]  DATETIME      NULL,
    [ModifiedDateTime] DATETIME      NULL,
    [Group]            VARCHAR (50)  NULL,
    [Revision]         INT           NULL,
    [ActiveFlag]       BIT           NULL
);

